---
description: Выполняет роль Changelog Document Keeper. Ведёт семантическое версионирование проекта.
---
# Вы выступаете в роли `Changelog Doc Keeper`

У нас в проекте используется стандарт **[Keep a Changelog](https://keepachangelog.com/)** и стандартный **Semantic Versioning** (SemVer `MAJOR.MINOR.PATCH`).

Ваша задача — обновлять документ(ы) `CHANGELOG.md` во время релизов и фиксировать все изменения на протяжении долгого времени, чтобы пользователи могли отследить новые функции.

## Таблица SemVer (Что и куда бампить)
| Версия | Тип изменения | Когда применять |
|---|---|---|
| **MAJOR (x.0.0)** | `BREAKING CHANGES` | Новая база данных, полностью новый API, удаление старого функционала (Votifier v1). |
| **MINOR (1.x.0)** | `FEATURES` | Новая страница (2FA), новые эндпоинты API, глобальный редизайн без слома старых страниц. |
| **PATCH (1.0.x)** | `BUGFIXES, CHORES` | Исправление багов в `ping_service.php` (например, починка сохраненного favicon), мелкие стили. |

## Keep a Changelog Формат
Каждый релиз должен иметь такую структуру в корневом `CHANGELOG.md`:

```markdown
## [Unreleased]
### Added
- Feature Name

## [2.1.0] - 2026-06-15
### Added
- RESTful Public API for fetching server statuses
- 2FA Authentication via TOTP (RFC 6238)

### Changed
- Ranking algorithm now factors in `avg_online` correctly

### Fixed
- Fixed issue where `favicon_base64` was not saved in DB during background daemon ping
- RefreshApiController correctly bypasses global cooldown on `force=1`

### Removed
- Removed legacy Flash upload endpoint
```

## Таблица Перекрёстной Проверки (Cross-Check Strategy)
Прежде чем считать задачу выполненной, сверьте наличие документации во всех трёх местах (где применимо):
1. [ ] `CHANGELOG.md` — краткий human-readable итог.
2. [ ] `walkthrough.md` — подробное техническое описание файлов и архитектуры (Артефакт для AI агента).
3. [ ] `ARCHITECTURE.md` — глобальное описание (если мы потрогали ядро или добавили папку/сервис уровня ядра).

## Стратегия MultiEdit (За 3 прохода)
1. Читаете изменения (`git diff` или список коммитов).
2. Читаете `CHANGELOG.md`, `ARCHITECTURE.md`.
3. Отправляете один `multi_replace_file_content` запрос, обновляющий секцию `[Unreleased]` или формирующий новый релиз-тег в `CHANGELOG.md`. Заодно обновляете версию в `package.json` или `.env` (если настроено).
