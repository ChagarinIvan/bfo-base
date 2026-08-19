---
description: "Task list — обновление технологического стека до latest"
---

# Tasks: Обновление технологического стека до latest

**Input**: Design documents from `/specs/001-upgrade-stack/`

**Prerequisites**: plan.md, spec.md, research.md, quickstart.md

**Tests**: Новые тесты по TDD НЕ пишутся. Используется существующая регресс-сеть (~115 тест-файлов) +
добавление базовой проверки только там, где покрытия не хватает. Прогон гейтов — часть DoD каждого шага.

## Формат: `[ID] [P?] [Story] Description`

- **[P]**: можно параллельно (разные файлы, нет зависимостей). В этой фиче почти всё **последовательно**:
  каждый шаг меняет `composer.json`/`composer.lock`, поэтому шаги идут по очереди. `[P]` помечены лишь
  чисто исследовательские задачи, не трогающие общих файлов.
- **[Story]**: US1–US5 из spec.md (для трассируемости).

## ⚠️ Порядок фаз = порядок зависимостей (не приоритет спеки)

Фича последовательная. Порядок выполнения взят из [research.md](./research.md) (спека это допускала):
безопасные обновления → сжатие поверхности + phpspreadsheet (гейт для PHP 8.5) → Laravel 11→12→13 →
PHP 8.5 → инфра. Поэтому **US3 (библиотеки, P2) выполняется раньше US2 (Laravel, P1)**: phpspreadsheet
блокирует PHP 8.5, а удаление dbal/predis и снятие пина guzzle сокращают конфликтную поверхность перед
мажорным прыжком Laravel.

## Гейт после КАЖДОГО шага (DoD, см. [quickstart.md](./quickstart.md))

`composer test` → `composer stan` → `composer cs` → `composer rector --dry-run` (просмотр diff) →
`php artisan serve` (smoke) → отдельный коммит. Переход к следующему шагу — только когда всё зелёное.

---

## Phase 1: Setup (базовая линия)

**Purpose**: Зафиксировать «зелёное до» и подготовить ветку.

- [X] T001 Убедиться, что работа идёт в ветке `001-upgrade-stack` (создать от `master`, если ещё нет) — работаем в ветке `upgrade-stack`
- [X] T002 Зафиксировать базовую линию: прогнать `composer install`, `composer test`, `composer stan`, `composer cs`; записать результат. Любой предсуществующий «красный» гейт починить ДО начала апгрейда — baseline зелёный: test 237 ✅, stan ✅, cs ✅. Починены: stan `--memory-limit=1G`, cs exclude генерируемых каталогов, 8 избыточных `assertContainsOnlyInstancesOf` → `assertCount`
- [ ] T002a Составить карту слабого тест-покрытия на участках, затрагиваемых апгрейдом (парсеры Excel `tests/Models/Parser/*`; CSRF/сессии/кэш; `HasUuids`; HTTP-роуты); где покрытия нет — добавить baseline-тест ДО соответствующего шага (опора под FR-005/SC-002)

**Checkpoint**: базовая линия зелёная — можно апгрейдить.

---

## Phase 2: Foundational (снятие неизвестных `[verify-live]`)

**Purpose**: Разрешить версии/совместимость, которые ресёрч не смог подтвердить вживую, ДО фиксации версий.

**⚠️ CRITICAL**: результаты этой фазы влияют на целевые версии во всех последующих шагах.

- [X] T003 [P] Подтвердить на packagist точные latest-версии целей — зафиксировано в research.md: L13.26.1, phpspreadsheet 5.9.0, horizon 5.48.3, sentry 4.27.0, guzzle 8.0.2/7.15.3, predis 3.6.0; rector-laravel 2.5.0 содержит `UP_TO_LARAVEL_130` (кумулятивный)
- [X] T004 [P] Подтвердить совместимость — horizon 5.48.3 (`illuminate ^13.0`) и sentry 4.27.0 (`^13.0`) совместимы с L12/L13; guzzle 8.0.2 стабилен и L13 его допускает (`^7.8.2 || ^8.0`); пин снимаем только до `^7.10` (T011). ⚠️ sentry пин `4.24.0` надо снять
- [X] T005 [P] C-расширения под PHP 8.5 — gd/pdo_mysql/mbstring/zip/exif/pcntl это core/bundled (компилируются из `php:8.5-fpm`); phpredis ставится `pecl install redis` (ext 6.x поддерживает 8.5). ⚠️ phpredis отсутствует в Dockerfile — добавить в T010/T017. Финальная проверка = сборка образа

**Checkpoint**: целевые версии и блокеры известны — апгрейд идёт по фактам.

---

## Phase 3: User Story 1 — Безопасные обновления + замена заброшенного пакета (P1) 🎯 MVP

**Goal**: Поднять всё, что обновляется в рамках semver, и заменить abandoned `rector/rector-laravel`.

**Independent Test**: все гейты зелёные; `composer` не сообщает abandoned по rector-части; приложение работает локально.

- [X] T006 [US1] Заменить `rector/rector-laravel` на `driftingly/rector-laravel` — установлен `^2.5`, старый удалён; `rector.php` не менялся (тот же namespace `RectorLaravel\`); rector грузится, stan/cs зелёные
- [X] T007 [US1] Обновить dev-инструменты в рамках semver — php-cs-fixer 3.94→3.95, phpstan 2.1→2.2, larastan 3.9→3.10, phpunit 13.1→13.3, mockery 1.6.12→1.6.14, rector 2.4→2.6; stan/cs/test зелёные
- [X] T008 [US1] Обновить runtime-миноры — horizon 5.45→5.48, sentry 4.24→4.30; чекпоинт US1: stan/cs/test (237) зелёные

**Checkpoint**: US1 завершена — минимальный жизнеспособный результат достигнут.

---

## Phase 4: User Story 3 — Сжатие поверхности библиотек + phpspreadsheet (P2, выполняется рано)

**Goal**: Удалить неиспользуемые dbal и predis, снять пин guzzle, поднять phpspreadsheet (гейт для PHP 8.5).

**Independent Test**: гейты зелёные; парсеры Excel работают идентично; кэш/очереди работают; ноль удалённых пакетов в lock.

- [X] T009 [US3] Удалить `doctrine/dbal` — пакет удалён из `composer.json`/`composer.lock` (+транзитивные doctrine/*); мёртвый `use Doctrine\DBAL\Types\Type;` убран из миграции `2024_03_12_100012_...`. Гейты зелёные: test 237, stan, cs. `migrate --pretend` не гонялся (нет `->change()` в миграциях, риск нулевой). Коммит `df297dd`
- [X] T010 [US3] **Разворот Decision 2: стандартизация на predis, а не phpredis** (по решению владельца). phpredis не установлен нигде, а `.env`/`.env.example` реально используют predis → переход на phpredis был бы изменением поведения. Вместо удаления — апгрейд `predis/predis` `^1.1` (1.1.10, 2022, мёртвый) → `^2.3 || ^3.0` (установлен 3.6.0). L11 рекомендует `^2.3`, L13 — `^2.3 || ^3.0`, predis 3.x работает на PHP 8.5. `REDIS_CLIENT=predis` без изменений, Dockerfile не трогаем. Проверено на **живом redis-dev**: Cache roundtrip + raw redis OK (клиент `Predis\Client`). Гейты: test 237, stan, cs, rector, boot — зелёные

> **Починка предсуществующего rector-гейта** (не в исходном плане, побочная находка): rector был красным
> с «Phase 3» (бамп rector 2.4→2.6 в T007 убрал константу `PHPUnitSetList::PHPUNIT_110`), но T002 rector не
> прогонял. Исправлено в `rector.php`: убрана мёртвая константа, удалён никогда не регистрируемый
> `ExplicitBoolCompareRector` из `withSkip`. Применены 15 накопившихся модернизаций (типы возврата
> стрелочных функций, чистка `@var`/union-докблоков, `declare(strict_types=1)`,
> `expectExceptionMessage`→`expectExceptionMessageIsOrContains`). ⚠️ Автозамена `Blade::component`→
> `aliasComponent` (правило laravel70) **ошибочна** для классовых компонентов — ломала bootstrap (поймал
> stan); откачена + добавлен точечный `RenameMethodRector` skip на `ViewProvider.php`.
- [X] T011 [US3] Снят точечный пин guzzle: `7.10.0` → `^7.10` в `composer.json`; установлен стабильный **7.15.3** (+ guzzlehttp/promises 2.5.2, uri-template 1.0.10). Из-за `minimum-stability: dev` частичный `composer update guzzlehttp/guzzle` зацепил `7.10.x-dev` — исправлено через `composer update "guzzlehttp/*" --with-all-dependencies` (взял стабильный тег). Прямого guzzle-кода нет. Гейты: test 237, stan, cs — зелёные. ⚠️ В рабочем дереве также починка `rector.php` (восстановлены `AssertSeeToAssertSeeHtmlRector` + skip `RenameMethodRector` на `ViewProvider`, без которых rector-гейт красный)
- [ ] T012 [US3] Поднять `phpoffice/phpspreadsheet` до `^5.0` в `composer.json`; `composer update`; прогнать 18 parser-тестов (`tests/Models/Parser/*`); при необходимости адаптировать 4 парсера в `app/Models/Parser/` (Reader-only, риск низкий); вручную импортировать реальные `.xlsx` и `.xls`; гейты; коммит

**Checkpoint**: поверхность сжата, phpspreadsheet готов — PHP 8.5 разблокирован по этой линии.

---

## Phase 5: User Story 2 — Обновление Laravel 11 → 12 → 13 (P1)

**Goal**: Поднять фреймворк по одному мажору с Rector-автоматизацией.

**Independent Test**: после 11→12 гейты зелёные и приложение работает; отдельно после 12→13 — то же.

- [ ] T013 [US2] Поднять уровень Laravel в `rector.php`: `LaravelLevelSetList::UP_TO_LARAVEL_110` → `UP_TO_LARAVEL_120`
- [ ] T014 [US2] Поднять `laravel/framework` до `^12` в `composer.json`; `composer update`; прогнать `composer rector` (сет `UP_TO_LARAVEL_120`); обработать: `HasUuids`→UUIDv7 (при нужде `HasVersion4Uuids`), Carbon 2→3, `image`-валидация без SVG, `local` disk root `storage/app/private`; сверить horizon/sentry; гейты; коммит
- [ ] T015 [US2] Добавить сет `LaravelSetList::LARAVEL_130` в `rector.php`
- [ ] T016 [US2] Поднять `laravel/framework` до `^13` в `composer.json`; `composer update`; прогнать `composer rector` (сет `LARAVEL_130`); обработать: CSRF `VerifyCsrfToken`→`PreventRequestForgery`, `serializable_classes`=false (кэш объектов), сериализацию сессий `json`, `upsert` с пустым `uniqueBy`; проверить клеш глобальных `array_first()/array_last()` (полифилл 8.5) с легаси-хелперами; гейты; коммит

**Checkpoint**: приложение на Laravel 13, гейты зелёные.

---

## Phase 6: User Story 4 — Обновление рантайма PHP до 8.5 (P2)

**Goal**: Перевести рантайм на PHP 8.5 (после phpspreadsheet и подтверждения расширений из T005).

**Independent Test**: на PHP 8.5 все гейты зелёные и приложение поднимается локально.

- [ ] T017 [US4] Обновить `Dockerfile`: `FROM php:8.4-fpm` → `php:8.5-fpm` (закрепить minor); пересобрать образ приложения
- [ ] T018 [US4] Обновить ограничение PHP в `composer.json` (напр. `^8.4` → допускающее 8.5); `composer update`; прогнать гейты на 8.5; проверить предупреждения по PDO-константам (`PDO::MYSQL_ATTR_*`) и депрекейтам кастов; коммит

**Checkpoint**: стек приложения на PHP 8.5, гейты зелёные.

---

## Phase 7: User Story 5 — Обновление инфраструктурных образов (P3)

**Goal**: Поднять образы MySQL/Redis/Nginx, зафиксировать теги в репозитории.

**Independent Test**: приложение поднимается на новых образах, данные MySQL сохранены, теги не `latest`.

- [ ] T019 [US5] Снять бэкап БД перед инфра-изменениями: `docker compose exec db mysqldump ... > backup.sql`
- [ ] T020 [US5] Поднять образ MySQL `8.0.27` → `8.4` (LTS) в `docker-compose.yml.example` (и в боевом compose при деплое); пересоздать контейнер; проверить сохранность данных и работу приложения; коммит
- [ ] T021 [US5] Поднять образ Redis `6.2.6` → 7/8 в `docker-compose.yml.example`; проверить кэш и очереди; коммит
- [ ] T022 [US5] Поднять образ Nginx `1.21.4-alpine` → текущий стабильный в `docker-compose.yml.example`; проверить веб-слой; коммит

**Checkpoint**: инфра на актуальных зафиксированных версиях.

---

## Phase 8: Polish & Cross-Cutting

**Purpose**: Финальная сверка критериев приёмки и синхронизация документации.

- [ ] T023 Финальный полный прогон гейтов на собранном стеке; проверить SC-004: `composer show` — ноль abandoned; в `docker-compose.yml.example` ноль тегов `latest`
- [ ] T024 [P] Обновить раздел «Развёртывание и окружения» в `.specify/memory/constitution.md` под новые версии (PHP 8.5, MySQL 8.4, Redis 7/8, Nginx) — PATCH-бамп
- [ ] T025 [P] Свериться со всеми SC-001…SC-006 из spec.md; отметить выполненные критерии

---

## Dependencies (порядок завершения)

```text
Phase 1 (Setup) → Phase 2 (Foundational, verify-live)
   → Phase 3 US1 (safe semver + rector-laravel)   [MVP]
   → Phase 4 US3 (remove dbal → remove predis → unpin guzzle → phpspreadsheet 5)
   → Phase 5 US2 (Laravel 11→12 → 12→13)
   → Phase 6 US4 (PHP 8.5)          [требует T005 + Phase 4 phpspreadsheet]
   → Phase 7 US5 (MySQL → Redis → Nginx)
   → Phase 8 (Polish)
```

Внутри Phase 4: T009 → T010 → T011 → T012 (последовательно, общий `composer.lock`).
Внутри Phase 5: T013 → T014 → T015 → T016 (нельзя 13 раньше 12).
Ключевая зависимость: **Phase 6 (PHP 8.5) требует завершённого T012 (phpspreadsheet)** и подтверждённого T005.

## Параллельные возможности

Почти всё последовательно (общий `composer.lock`). Реально параллельны только исследовательские задачи
фазы 2 — T003, T004, T005 (`[P]`), и в самом конце T024/T025 (`[P]`, разные файлы, без composer).

## Implementation Strategy

- **MVP** = Phase 3 (US1): безопасные обновления + снятие заброшенного пакета. Уже даёт ценность и
  оставляет систему зелёной.
- Дальше — строго по одному шагу с гейтом и отдельным коммитом; при блокировке шага (несовместимость)
  фиксируем в research.md и не ломаем зелёную линию предыдущих (FR-011).
- Инфра (Phase 7) применяется в деплое отдельно, с бэкапом (см. quickstart.md).
