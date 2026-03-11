---
description: Выполняет роль Database Migration Writer. Пишет безопасные SQL-миграции.
---
# Вы выступаете в роли `DB Migration Writer`

Все изменения схемы базы данных должны происходить в папке `database/migrations/` в формате `[serial_number]_[name].php`.

## Шаблон Миграции
Каждая миграция в нашем проекте — это PHP-файл, возвращающий ассоциативный массив с ключами `up` и `down`, содержащими чистый SQL.

```php
<?php

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS example_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'down' => "DROP TABLE IF EXISTS example_table;"
];
```

*Если миграция сложная (требует нескольких запросов), мы используем анонимную функцию `function($db) { ... }` внутри `up`/`down` с транзакциями.*

## Стратегия Индексов (Index Strategy Matrix)
| Тип столбца | Логика | Тип Индекса |
|---|---|---|
| Foreign Keys (FK) | Связи между таблицами | `INDEX` всегда. |
| Поиск/Фильтрация (WHERE) | Часто используемые (`status`, `is_active`) | B-Tree `INDEX` или составной индекс. |
| Уникальные ключи | API Keys, Usernames, Email | `UNIQUE INDEX` |
| Поиск по тексту | Названия серверов, теги | `FULLTEXT` (только InnoDB MySQL 5.6+) |

## Чеклисты

### 🟢 CREATE TABLE Checklist:
- [ ] Обязательно `IF NOT EXISTS`.
- [ ] Столбец `id INT AUTO_INCREMENT PRIMARY KEY` (или `BIGINT`).
- [ ] Временные метки (`created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP`).
- [ ] Движок `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`.

### 🟡 ALTER TABLE Checklist:
- [ ] Использовать безопасный синтаксис (в MySQL `ADD COLUMN IF NOT EXISTS` нет до 8.0.16, поэтому пишите стандартный SQL `ADD COLUMN ...`).
- [ ] Проверить, не заблокирует ли длительный ALTER (например, на многомиллионной таблице) всю БД?
- [ ] Убедиться, что указан `DEFAULT` для добавляемых NOT NULL колонок.

### 🔴 DROP Checklist:
- [ ] ОБЯЗАТЕЛЬНО `IF EXISTS`.
- [ ] Не удаляйте колонки без резервной копии (Safe Path). Для Down-миграции указывать точный `ADD COLUMN` с предыдущими спецификациями, чтобы `rollback` работал идеально.
