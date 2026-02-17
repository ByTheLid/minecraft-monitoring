Ты сеньёр веб разработчик. Твоя задача реализовать проект

# Minecraft Server Monitoring — План реализации

## Обзор

Платформа мониторинга серверов Minecraft с рейтингом, голосованием и boost-системой.  
Стек: PHP 8.2+, MySQL 8+, Vanilla JS. Стиль: Pixel UI + Cyber Tech.

---

## Фаза 0 — Подготовка инфраструктуры (2–3 дня)

**Цель:** Рабочее окружение, в котором можно сразу писать код.

### Задачи

1. **Структура проекта и автозагрузка**
   - Инициализация Composer (`PSR-4` autoload)
   - Структура каталогов:
     ```
     /monitoring.local
       /Controllers
       /Models
       /Services
       /Middleware
       /Views
     /config
     /public (точка входа index.php)
     /routes
     /database
       /migrations
       /seeds
     /storage
       /logs
       /cache
     /cron
     ```
   - Единая точка входа через `public/index.php`

2. **Ядро фреймворка (MVC)**
   - Router (поддержка GET/POST/PUT/DELETE, параметры маршрутов, middleware-цепочки)
   - Base Controller (JSON-ответы, рендер шаблонов, валидация)
   - Base Model (PDO-обёртка, query builder, prepared statements)
   - Request / Response классы
   - Middleware pipeline (auth, rate-limit, CSRF, CORS)
   - Exception handler с логированием

3. **База данных**
   - Система миграций (up/down, версионирование)
   - Seed-скрипты для тестовых данных
   - Конфиг подключения через `.env`

4. **Инструменты разработки**
   - `.env` + config loader
   - Логгер (файл + уровни: debug/info/error)
   - CSRF-токены для форм
   - Helper-функции (redirect, sanitize, flash-сообщения)

### Критерий готовности

- `GET /health` возвращает `{"status": "ok"}` — роутер, контроллер и БД работают.

---

## Фаза 1 — Аутентификация и управление серверами (5–7 дней)

**Цель:** Пользователь может зарегистрироваться, войти и управлять своими серверами.

### 1.1 Аутентификация

**Таблицы:**

```sql
users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(32) UNIQUE NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') DEFAULT 'user',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)

sessions (
  id CHAR(64) PRIMARY KEY,
  user_id INT NOT NULL,
  ip_address VARCHAR(45),
  user_agent VARCHAR(255),
  expires_at TIMESTAMP NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)
```

**Функциональность:**

- Регистрация: валидация username (3–32 символа, `[a-zA-Z0-9_]`), email, пароль (минимум 8 символов)
- Хеширование: `password_hash()` с `PASSWORD_BCRYPT`
- Авторизация: сессии через secure cookie (HttpOnly, SameSite=Strict)
- Защита от брутфорса: лимит 5 попыток / 15 минут по IP
- Выход: удаление сессии из БД + очистка cookie

### 1.2 CRUD серверов

**Таблица:**

```sql
servers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  ip VARCHAR(255) NOT NULL,
  port SMALLINT UNSIGNED DEFAULT 25565,
  description TEXT,
  website VARCHAR(255),
  banner_url VARCHAR(255),
  tags JSON,
  is_active TINYINT(1) DEFAULT 1,
  is_approved TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_server (ip, port)
)
```

**Функциональность:**

- Добавление сервера: валидация IP (IPv4/домен), порт (1–65535), проверка дублей
- Редактирование: только владелец или админ
- Удаление: soft-delete через `is_active = 0`
- Лимит: максимум 5 серверов на пользователя (настраивается)
- Модерация: новые серверы `is_approved = 0`, видны только после одобрения

### Критерий готовности

- Пользователь регистрируется, входит, добавляет сервер, редактирует, удаляет. Всё через UI-формы.

---

## Фаза 2 — Пинг-сервис и сбор статистики

**Цель:** Система автоматически проверяет серверы и копит историю.

### 2.1 Ping Service

**Протокол:** Minecraft Server List Ping (SLP) — отправка handshake + status request по TCP.

**Получаемые данные:**

- Online/offline статус
- Текущие игроки / максимум
- Версия сервера (строка + protocol version)
- MOTD
- Время отклика (ping, ms)
- Favicon (base64)

**Таблица статистики:**

```sql
server_stats (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  server_id INT NOT NULL,
  is_online TINYINT(1) NOT NULL,
  players_online SMALLINT UNSIGNED DEFAULT 0,
  players_max SMALLINT UNSIGNED DEFAULT 0,
  version VARCHAR(64),
  ping_ms SMALLINT UNSIGNED,
  motd VARCHAR(512),
  checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
  INDEX idx_server_checked (server_id, checked_at)
)
```

**Таблица кешированного состояния:**

```sql
server_status_cache (
  server_id INT PRIMARY KEY,
  is_online TINYINT(1),
  players_online SMALLINT UNSIGNED,
  players_max SMALLINT UNSIGNED,
  version VARCHAR(64),
  ping_ms SMALLINT UNSIGNED,
  motd VARCHAR(512),
  favicon_base64 TEXT,
  last_checked_at TIMESTAMP,
  FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
)
```

### 2.2 CRON-задача

```
*/3 * * * * php /path/to/cron/ping.php
```

**Логика:**

1. Выбрать все активные одобренные серверы
2. Пинговать параллельно (batch по 50, socket timeout 5 сек)
3. Записать результат в `server_stats`
4. Обновить `server_status_cache`
5. Логировать ошибки

**Оптимизации:**

- Таймаут соединения: 5 секунд
- Batch-обработка — не пинговать все 1000+ разом
- Ретрай: если offline — повторная проверка через 1 минуту перед пометкой
- Очистка старых записей: хранить детальную статистику 30 дней, агрегаты — бессрочно

### 2.3 Агрегация для графиков

```sql
server_stats_hourly (
  server_id INT,
  hour TIMESTAMP,
  avg_players DECIMAL(8,2),
  max_players SMALLINT,
  uptime_percent DECIMAL(5,2),
  avg_ping DECIMAL(8,2),
  PRIMARY KEY (server_id, hour)
)
```

CRON-задача агрегации — 1 раз в час. Данные из `server_stats` → `server_stats_hourly`.

### Критерий готовности

- CRON пингует тестовый сервер, данные появляются в `server_stats`, кеш обновляется.

---

## Фаза 3 — Рейтинг, голосование и Boost

**Цель:** Рабочая система ранжирования серверов.

### 3.1 Голосование

**Таблица:**

```sql
votes (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  server_id INT NOT NULL,
  user_id INT,
  ip_address VARCHAR(45) NOT NULL,
  voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
  INDEX idx_vote_lookup (server_id, ip_address, voted_at)
)
```

**Правила:**

- 1 голос за сервер в 24 часа
- Проверка по `user_id` (если авторизован) И по `ip_address`
- Нельзя голосовать за свой сервер
- Rate-limit: максимум 10 голосов в час с одного IP (защита от абьюза)

### 3.2 Boost-система

**Таблицы:**

```sql
boost_packages (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(64) NOT NULL,
  points INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  duration_days INT NOT NULL,
  is_active TINYINT(1) DEFAULT 1
)

boost_purchases (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  server_id INT NOT NULL,
  package_id INT NOT NULL,
  points INT NOT NULL,
  activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at TIMESTAMP NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (server_id) REFERENCES servers(id),
  INDEX idx_active_boosts (server_id, expires_at)
)
```

### 3.3 Формула рейтинга

```
RANK = (V × Kv) + (B × Kb) + (O × Ko) + (U × Ku)

V = количество голосов за последние 30 дней
B = сумма активных (не истёкших) boost-баллов
O = средний онлайн за 7 дней (нормализован 0–100)
U = uptime за 7 дней (% → 0–100)

Коэффициенты (по умолчанию):
  Kv = 1.0  (голоса)
  Kb = 0.5  (boost)
  Ko = 0.3  (онлайн)
  Ku = 0.2  (uptime)
```

**Таблица рейтинга (кеш):**

```sql
server_rankings (
  server_id INT PRIMARY KEY,
  rank_score DECIMAL(12,4) NOT NULL,
  vote_count INT DEFAULT 0,
  boost_points INT DEFAULT 0,
  avg_online DECIMAL(8,2) DEFAULT 0,
  uptime_percent DECIMAL(5,2) DEFAULT 0,
  calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
  INDEX idx_rank (rank_score DESC)
)
```

**Настройки коэффициентов:**

```sql
settings (
  `key` VARCHAR(64) PRIMARY KEY,
  value TEXT NOT NULL,
  description VARCHAR(255)
)
```

Пересчёт рейтинга: CRON каждые 15 минут.

### Критерий готовности

- Голос увеличивает рейтинг, boost увеличивает рейтинг, серверы сортируются по `rank_score`.

---

## Фаза 4 — Публичный UI

**Цель:** Полноценный фронтенд с игровым стилем.

### 4.1 Дизайн-система

**CSS-переменные (Dark тема):**

```css
:root {
	--bg-primary: #1a1a2e;
	--bg-secondary: #16213e;
	--bg-card: #0f3460;
	--text-primary: #e0e0e0;
	--text-secondary: #a0a0a0;
	--accent-green: #00ff88;
	--accent-blue: #00d4ff;
	--accent-red: #ff4444;
	--accent-gold: #ffd700;
	--glow-green: 0 0 10px rgba(0, 255, 136, 0.3);
	--glow-blue: 0 0 10px rgba(0, 212, 255, 0.3);
	--pixel-border: 3px solid;
	--font-pixel: "Press Start 2P", monospace;
	--font-body: "Roboto Mono", monospace;
}
```

**Компоненты:**

- Pixel Button (Minecraft-стиль, glow при hover)
- Server Card (иконка, статус, игроки, пинг, кнопка голоса)
- Status Badge (зелёный пульс — online, серый — offline)
- Progress Bar (пиксельный, для заполненности сервера)
- Modal (пиксельная рамка, backdrop blur)
- Toast уведомления
- Табы, пагинация

### 4.2 Страницы

| Страница        | URL                   | Описание                              |
| --------------- | --------------------- | ------------------------------------- |
| Главная         | `/`                   | ТОП серверов, поиск, фильтры, контент |
| Каталог         | `/servers`            | Полный список, сортировка, пагинация  |
| Сервер          | `/server/{id}`        | Детали, график, голосование           |
| Личный кабинет  | `/dashboard`          | Мои серверы, статистика, boost        |
| Добавить сервер | `/dashboard/add`      | Форма добавления                      |
| Авторизация     | `/login`, `/register` | Формы входа/регистрации               |
| Посты           | `/posts`              | Список постов                         |
| Пост            | `/post/{id}`          | Отдельный пост                        |

### 4.3 JavaScript-модули

```
/public/js/
  app.js          — инициализация, роутинг
  api.js          — Axios-обёртка для API-запросов
  theme.js        — переключение dark/light
  servers.js      — загрузка, фильтрация, сортировка списка
  serverCard.js   — рендер карточки сервера
  chart.js        — графики активности (Chart.js)
  vote.js         — логика голосования
  dashboard.js    — личный кабинет
  utils.js        — debounce, форматирование, sanitize
```

### 4.4 Графики (Chart.js)

- **Активность за 24ч / 7 дней / 30 дней** — line chart, игроки онлайн по времени
- **Uptime** — doughnut chart
- **Голоса за месяц** — bar chart

### 4.5 Тема Light

Отдельный набор CSS-переменных, переключение через `data-theme` на `<html>`, сохранение в `localStorage`.

### Критерий готовности

- Главная загружается, серверы отображаются карточками, фильтры/поиск работают, голосование через UI, графики рисуются.

---

## Фаза 5 — API (3–4 дня)

**Цель:** Документированный REST API.

### Публичные эндпоинты

| Метод | URL                       | Описание                                         |
| ----- | ------------------------- | ------------------------------------------------ |
| GET   | `/api/servers`            | Список серверов (пагинация, фильтры, сортировка) |
| GET   | `/api/servers/{id}`       | Информация о сервере                             |
| GET   | `/api/servers/{id}/stats` | Статистика (период: 24h/7d/30d)                  |
| POST  | `/api/servers/{id}/vote`  | Голосование                                      |

**Параметры GET `/api/servers`:**

- `page` (int) — страница
- `per_page` (int, max 50) — количество
- `sort` (rank/players/votes/newest) — сортировка
- `search` (string) — поиск по имени
- `version` (string) — фильтр по версии
- `status` (online/offline/all) — фильтр по статусу
- `tags` (string, comma-separated) — фильтр по тегам

### Приватные эндпоинты (требуют авторизацию)

| Метод  | URL                    | Описание                |
| ------ | ---------------------- | ----------------------- |
| POST   | `/api/servers`         | Добавить сервер         |
| PUT    | `/api/servers/{id}`    | Обновить сервер         |
| DELETE | `/api/servers/{id}`    | Удалить сервер          |
| POST   | `/api/boost/purchase`  | Купить boost            |
| GET    | `/api/dashboard/stats` | Статистика пользователя |

### Админские эндпоинты

| Метод | URL                               | Описание                           |
| ----- | --------------------------------- | ---------------------------------- |
| GET   | `/api/admin/servers`              | Все серверы (включая неодобренные) |
| PUT   | `/api/admin/servers/{id}/approve` | Одобрить сервер                    |
| PUT   | `/api/admin/settings`             | Обновить коэффициенты              |
| GET   | `/api/admin/logs`                 | Просмотр логов                     |

**Формат ответов:**

```json
{
  "success": true,
  "data": { ... },
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 342
  }
}
```

**Ошибки:**

```json
{
	"success": false,
	"error": {
		"code": "VALIDATION_ERROR",
		"message": "Invalid port number",
		"details": { "port": "Must be between 1 and 65535" }
	}
}
```

### Критерий готовности

- Все эндпоинты отвечают корректно, ошибки возвращают правильные HTTP-коды, rate-limit работает.

---

## Фаза 6 — Админ-панель

**Цель:** Полный контроль над платформой.

### Функции

1. **Дашборд админа**
   - Общая статистика: серверов, пользователей, голосов за сегодня
   - Новые серверы на модерацию
   - Последние действия (лог)

2. **Управление серверами**
   - Список всех серверов (одобренные / на модерации / заблокированные)
   - Одобрение / отклонение / блокировка
   - Редактирование любого сервера

3. **Управление пользователями**
   - Список, поиск, фильтрация
   - Блокировка / разблокировка
   - Смена роли

4. **Контент (посты)**
   - CRUD постов (заголовок, текст, изображение, категория)
   - Публикация / черновики

5. **Настройки рейтинга**
   - Редактирование коэффициентов формулы
   - Управление boost-пакетами (цены, сроки, баллы)

6. **Логи**
   - Просмотр действий пользователей
   - Фильтрация по типу, дате, пользователю

### Критерий готовности

- Админ может одобрить сервер, заблокировать пользователя, изменить коэффициенты — всё отражается на публичной части.

---

## Фаза 7 — Контент-система

**Цель:** Посты/новости на платформе.

**Таблица:**

```sql
posts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  author_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  content TEXT NOT NULL,
  cover_image VARCHAR(255),
  category ENUM('news','guide','update') DEFAULT 'news',
  is_published TINYINT(1) DEFAULT 0,
  published_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id)
)
```

- Отображение на главной (последние 3 поста)
- Страница списка постов с пагинацией
- Страница отдельного поста
- SEO: title, description, Open Graph теги

### Критерий готовности

- Админ создаёт пост, он появляется на главной и по прямой ссылке.

---

## Фаза 8 — Безопасность и оптимизация

**Цель:** Продакшн-готовность.

### Безопасность

- **XSS:** `htmlspecialchars()` на весь пользовательский вывод
- **SQL Injection:** 100% prepared statements (проверить весь код)
- **CSRF:** токены на все POST/PUT/DELETE формы
- **Rate Limiting:** таблица `rate_limits` или in-memory (Redis если есть)
  - API: 60 запросов / минуту
  - Голосование: 10 / час
  - Авторизация: 5 попыток / 15 минут
- **Загрузка файлов:** проверка MIME, размера (макс 2MB), ресайз баннеров
- **HTTP-заголовки:** CSP, X-Frame-Options, X-Content-Type-Options, Strict-Transport-Security
- **Пароли:** только `password_hash()` / `password_verify()`

### Оптимизация БД

**Индексы:**

```sql
-- Основные
CREATE INDEX idx_servers_active ON servers(is_active, is_approved);
CREATE INDEX idx_stats_server_time ON server_stats(server_id, checked_at);
CREATE INDEX idx_votes_server_time ON votes(server_id, voted_at);
CREATE INDEX idx_boosts_active ON boost_purchases(server_id, expires_at);
CREATE INDEX idx_rankings_score ON server_rankings(rank_score DESC);

-- Поиск
CREATE FULLTEXT INDEX idx_servers_search ON servers(name, description);
```

### Оптимизация производительности

- Кеширование рейтинга в `server_rankings` (не считать на лету)
- Кеширование последнего статуса в `server_status_cache`
- Пагинация с `LIMIT/OFFSET` (или cursor-based для больших объёмов)
- Очистка старой статистики (CRON: ежедневно, хранить 30 дней детальных данных)
- Gzip-сжатие ответов
- Минификация CSS/JS для продакшна

### Критерий готовности

- Нагрузочное тестирование: 100 одновременных запросов отвечают < 500ms. XSS/SQLi-проверка пройдена.

---

## Сводная таблица

| Фаза | Название                   | Срок            | Зависимости  |
| ---- | -------------------------- | --------------- | ------------ |
| 0    | Инфраструктура             | 2–3 дня         | —            |
| 1    | Auth + CRUD серверов       | 5–7 дней        | Фаза 0       |
| 2    | Пинг-сервис                | 4–5 дней        | Фаза 1       |
| 3    | Рейтинг + Boost            | 5–6 дней        | Фаза 1, 2    |
| 4    | Публичный UI               | 7–10 дней       | Фаза 1, 2, 3 |
| 5    | REST API                   | 3–4 дня         | Фаза 1, 2, 3 |
| 6    | Админ-панель               | 4–5 дней        | Фаза 5       |
| 7    | Контент                    | 2–3 дня         | Фаза 1       |
| 8    | Безопасность + оптимизация | 3–4 дня         | Все фазы     |
|      | **Итого**                  | **~35–47 дней** |              |

> **Примечание:** Фазы 4, 5 и 7 могут идти параллельно после завершения фазы 3. Фаза 8 — финальный проход по всему проекту.

---

## CRON-расписание (итог)

| Задача                     | Интервал         | Скрипт                   |
| -------------------------- | ---------------- | ------------------------ |
| Пинг серверов              | каждые 3 мин     | `cron/ping.php`          |
| Пересчёт рейтинга          | каждые 15 мин    | `cron/rankings.php`      |
| Агрегация статистики       | каждый час       | `cron/aggregate.php`     |
| Очистка старых данных      | ежедневно, 03:00 | `cron/cleanup.php`       |
| Деактивация истёкших boost | каждые 15 мин    | `cron/expire_boosts.php` |

---

## Полная схема БД (сводка)

| Таблица               | Назначение               |
| --------------------- | ------------------------ |
| `users`               | Пользователи             |
| `sessions`            | Сессии авторизации       |
| `servers`             | Серверы Minecraft        |
| `server_stats`        | Детальная история пингов |
| `server_stats_hourly` | Почасовые агрегаты       |
| `server_status_cache` | Текущее состояние (кеш)  |
| `server_rankings`     | Кеш рейтинга             |
| `votes`               | Голоса                   |
| `boost_packages`      | Пакеты boost (тарифы)    |
| `boost_purchases`     | Покупки boost            |
| `posts`               | Контент/новости          |
| `settings`            | Настройки платформы      |
| `rate_limits`         | Rate limiting            |
