# Implementation Plan: Обновление технологического стека до latest

**Branch**: `001-upgrade-stack` | **Date**: 2026-08-19 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-upgrade-stack/spec.md`

## Summary

Инкрементальный апгрейд всего стека до актуальных версий без изменения поведения. Каждый шаг —
ровно одна вещь, отдельный коммит, зелёные гейты (test/stan/cs/rector + локальный запуск) перед
переходом дальше. По результатам ресёрча (см. [research.md](./research.md)) порядок уточнён относительно
спеки: phpspreadsheet поднимается рано (он блокирует PHP 8.5, при этом код-импакт минимален —
используется только Reader-API), doctrine/dbal и predis не апгрейдятся, а **удаляются** (не используются;
Redis уже на phpredis). Основной риск сосредоточен в переходах Laravel 11→12→13, автоматизируемых
Rector-сетами driftingly/rector-laravel.

## Technical Context

**Language/Version**: PHP 8.4.21 (composer floor `^8.2`) → цель **PHP 8.5** (после phpspreadsheet и
проверки сборок C-расширений).

**Primary Dependencies**: laravel/framework 11.51 → 13.x (по одному мажору); phpoffice/phpspreadsheet
1.30 → 5.x; guzzlehttp/guzzle `7.10.0` → `^7.10` (мажор 8 условен, ограничен constraint фреймворка);
doctrine/dbal 3.10 → **удалить**; predis/predis 1.1 → **удалить** (стандартизация на phpredis);
dev-инструменты (php-cs-fixer, larastan, phpstan, rector, mockery) + horizon + sentry-laravel — минор;
rector/rector-laravel (abandoned) → driftingly/rector-laravel.

**Storage**: MySQL 8.0.27 → 8.4 LTS (или новее); Redis 6.2 → 7/8 (через расширение phpredis).

**Testing**: PHPUnit 13.x; ~115 тест-файлов (unit по Domain/Application/Services + feature/request по
Bridge/Laravel/Http). `phpunit.xml`: QUEUE=sync, CACHE=array, SESSION=array. Гейты: `composer test`,
`composer stan` (PHPStan/Larastan lvl 5), `composer cs` (php-cs-fixer), `composer rector` (+ diff).

**Target Platform**: Docker Compose (nginx + php-fpm + mysql + redis) на AWS EC2 (Ubuntu); локально —
`php artisan serve` + MySQL. Прод использует старый `docker-compose` v1 (нужен top-level `version:`).

**Project Type**: Web-приложение (Laravel-монолит, слои DDD + Bridge к фреймворку, Blade-фронт в миграции на SPA).

**Performance Goals**: N/A — апгрейд не меняет поведение и производительность целями не ставит.

**Constraints**: Ноль изменений наблюдаемого поведения; каждый шаг откатываем отдельным коммитом;
версии образов фиксируются в репозитории (не `latest`); бэкап БД перед пересозданием MySQL.

**Scale/Scope**: ~7 групп шагов (см. ниже); ~115 тестов как регресс-сеть; 4 Excel-парсера — единственный
заметный код-импакт (и тот низкий, Reader-only).

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Сверка с конституцией v2.1.1:

| Принцип                           | Влияние фичи                                                                                     | Вердикт |
|-----------------------------------|--------------------------------------------------------------------------------------------------|---------|
| I. Слоистая архитектура           | Апгрейд не вводит новых репозиториев/сервисов и не меняет слои                                   | ✅ Соответствует |
| II. Без фасадов, интерфейсы       | Новых фасадов не добавляется; существующий код не рефакторится                                   | ✅ Соответствует |
| III. Тесты                        | Зелёные тесты — обязательный гейт каждого шага; при нехватке покрытия добавляем базовую проверку | ✅ Соответствует (центральный элемент) |
| IV. Целевая архитектура > латание | Кода не пишем; только версии. Удаление dbal/predis сокращает долг                                | ✅ Соответствует |
| V. Только вперёд                  | Фича — прямое воплощение принципа                                                                | ✅ Соответствует |
| Развёртывание                     | Версии образов фиксируются в репозитории; бэкап БД перед MySQL                                   | ✅ Соответствует (FR-008, FR-009) |
| Гейты качества                    | CS/STAN/Rector/тесты — определение готовности каждого шага                                       | ✅ Соответствует (FR-002) |

**Нарушений нет.** Complexity Tracking не требуется.

## Project Structure

### Documentation (this feature)

```text
specs/001-upgrade-stack/
├── plan.md              # Этот файл
├── research.md          # Phase 0 — факты и решения по версиям/порядку
├── quickstart.md        # Phase 1 — как валидировать каждый шаг
├── checklists/
│   └── requirements.md  # Чек-лист качества спеки
└── tasks.md             # Phase 2 — создаётся /speckit-tasks (НЕ здесь)
```

`data-model.md` и `contracts/` для этой фичи **не создаются**: апгрейд не вводит и не меняет доменные
сущности и не добавляет внешних интерфейсов/контрактов (FR-005 — поведение неизменно).

### Source Code (repository root)

Фича затрагивает конфигурацию зависимостей и окружения, а также единичные участки кода-совместимости.
Реальные пути, которые будут меняться:

```text
composer.json            # версии/удаление пакетов (dbal, predis, guzzle-constraint, laravel, phpspreadsheet, dev-tools)
composer.lock            # результат composer update
rector.php               # UP_TO_LARAVEL_110 → 120, затем LaravelSetList::LARAVEL_130; смена пакета на driftingly
Dockerfile               # php:8.4-fpm → php:8.5-fpm (шаг PHP)
docker-compose.yml.example  # теги образов MySQL/Redis/Nginx (шаг инфры)

app/Models/Parser/       # 4 парсера — правки под phpspreadsheet 5.x при необходимости (Reader-only, риск низкий)
database/migrations/     # удаление мёртвого use Doctrine\DBAL (шаг dbal)
config/                  # проверка cache/session/database при Laravel 13 (serializable_classes, session json)
app/                     # точечные правки совместимости Laravel 12/13 (UUIDv7, Carbon 3, CSRF rename) — по факту

tests/                   # регресс-сеть (~115 файлов); добавление базовых проверок при нехватке покрытия
```

**Structure Decision**: изменения точечные и рассредоточенные по конфигам/зависимостям; отдельная новая
структура каталогов не создаётся. Каждый шаг апгрейда = один коммит, затрагивающий минимальный набор
файлов из списка выше.

## Complexity Tracking

> Не заполняется — нарушений Constitution Check нет.
