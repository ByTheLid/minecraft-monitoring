# Архитектура и Паттерны Разработки (Minecraft Monitoring v2.0)

Это руководство предназначено для быстрого погружения в устройство проекта `minecraft-monitoring`. Мы используем невероятно быстрый, самописный **MVC (Model-View-Controller)** фреймворк без сторонних библиотек.

---

## 🏗 Основная структура

```text
/app
 ├── Controllers/  # Обработка запросов (User, Admin, Public)
 ├── Core/         # Ядро фреймворка (Router, App, QueryBuilder, Validator)
 ├── Middleware/   # Фильтры для роутов (Auth, CSRF, Admin)
 ├── Models/       # Работа с БД (Server, User, BoostPurchase)
 └── Views/        # HTML шаблоны (PHP), разбиты по секциям (admin/, user/, layouts/)
/public            # Точка входа (index.php), CSS, JS, Images
/routes            # Объявление всех URL (web.php, api.php)
/storage           # Хранилище логов, кеша, загрузок
/tests             # Легкие юнит-тесты для ключевых классов
```

---

## 🚦 Жизненный цикл запроса (Request Lifecycle)

1. **Точка Входа (`public/index.php`)**: Подключает `vendor/autoload.php` и ядро. Запускает обработчик ошибок (`ExceptionHandler::register()`) и инициализирует класс `App()`.
2. **Роутинг (`api.php` / `web.php`)**: URL сопоставляется с методом внутри объекта `Router`. Если роут защищен, сначала срабатывают классы из `App\Middleware\`.
3. **Контроллер (`App\Controllers\...`)**: Выполняет бизнес-логику (проверяет данные, запрашивает модель).
4. **Модель (`App\Models\...`)**: Общается с БД через наш безопасный `QueryBuilder`.
5. **View (`App\Core\Response::html()`)**: Контроллер возвращает отрендеренный шаблон из `app/Views/...` обратно пользователю.

---

## 💾 Паттерны работы с Базой Данных

Вся работа с БД должна происходить через класс `App\Core\QueryBuilder`. \
**ЗАПРЕЩАЕТСЯ** писать прямые `mysqli_query` или конкатенировать переменные в SQL строку во избежание SQL Инъекций!

### Правильный выбор (Select)
```php
// Вернет все активные сервера
$servers = App\Models\Server::db()
    ->where('status', 1)
    ->orderBy('rank_score', 'DESC')
    ->limit(10)
    ->get();
```

### Правильная вставка (Insert) и Обновление (Update)
```php
// Безопасное обновление через подготовленные выражения (Prepared Statements)
App\Models\User::update($userId, [
    'balance' => $newBalance,
    'updated_at' => date('Y-m-d H:i:s')
]);
```

---

## 🎨 Стандарты UI (Frontend Patterns)

### Уведомления (Toasts)
Мы отказались от всплывающих окон `alert()`. Для обратной связи с пользователем используйте `flash()` сессии в PHP.
```php
// В контроллере:
flash('success', 'Настройки успешно сохранены!');
flash('error', 'У вас недостаточно средств.');
```
*На фронтенде (в `layouts/main.php`) функция `show_flashes()` автоматически выведет красивый Toast (Зеленый или Красный).*

### Модальные окна
Модальные окна управляются через наши хелперы `openModal()` и `closeModal()`.
- Идентификаторы (id) модалок должны начинаться с `...Modal` (например, `voteModal`).
- Закрытие должно происходить по клику на фон (Overlay) и на клавишу ESC (уже вшито в `public/js/app.js`).

### Выбор иконок (FontAwesome Picker)
Для переиспользования компонента выбора иконок (FontAwesome), используйте `FontAwesomePicker`. Скрипт сам найдет элемент по классу `.icon-picker` и инициализируется.
Пример HTML структуры:
```html
<div class="form-group">
    <label>Icon</label>
    <div class="icon-picker" data-target="#icon_input_id" data-current="fa-solid fa-star"></div>
    <input type="hidden" name="icon" id="icon_input_id" value="fa-solid fa-star">
</div>
```
*Компонент поддерживает поиск по иконкам, визуальный предпросмотр и авто-комплит в скрытый input.*

### Глобальные настройки (Settings)
Настройки сайта (Название, Логотип, SEO ключи) хранятся в БД.
Вызываются во View через глобальный хелпер:
```php
<?= setting('site_name', 'Default Name') ?>
```
Для JSON-настроек (геймификация и др.) используйте `setting_json()`:
```php
$caps = setting_json('gamification_action_caps', ['vote' => 3]);
```

---

## 🔐 Безопасность (Security Conventions)

- **QueryBuilder** автоматически валидирует имена таблиц, колонок и SQL-операторов. Невалидные идентификаторы вызывают `InvalidArgumentException`.
- **UPDATE/DELETE без WHERE** запрещены на уровне QueryBuilder. Для массовых операций используйте `->whereRaw('1=1')`.
- **IP-адрес клиента** (`Request::ip()`) доверяет proxy-заголовкам (`X-Forwarded-For`, `X-Real-IP`) **только** от `127.0.0.1` / `::1`. На production за nginx/cloudflare это корректно.
- **CSRF токен** ротируется после каждой успешной проверки (replay protection).
- **Logout** работает через `POST /logout` с CSRF-токеном (защита от CSRF через `<img>`).
- **JSON body** ограничен 2 МБ для предотвращения OOM-атак.
- **Session ID** регенерируется при логине (`session_regenerate_id(true)`) для предотвращения Session Fixation.
- **Rate Limiting** использует атомарный `INSERT ... ON DUPLICATE KEY UPDATE` (без race condition).

---

## 🛠 Стабильность и Ошибки (Error Handling)

- Отлов `500 Internal Server Error` полностью автоматизирован. 
- Любое не пойманное исключение (`throw new Exception()`) или фатальная ошибка PHP запишется в файл `storage/logs/...log`.
- Если включен `APP_DEBUG=true` (в `.env`), пользователь увидит трассировку ошибки (Stacktrace).
- Если `APP_DEBUG=false`, отдается красивый, безопасный шаблон `app/Views/errors/500.php`.

---

## 🧪 Тестирование
Для запуска юнит-тестов (Test Runner):
```bash
php tests/run.php
```
Любые новые критичные модули (Оплата, Алгоритмы) должны покрываться тестами в папке `tests/` в обязательном порядке!
