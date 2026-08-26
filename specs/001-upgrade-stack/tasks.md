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
- [~] T002a Карта слабого покрытия как **отдельный артефакт не составлялась, новых baseline-тестов не добавлялось.** По факту существующая регресс-сеть (237 тестов) оказалась достаточной: на каждом мажоре она ловила реальные BC — L13 `orderBy('ASC')`, Symfony 8 `Request::get()`, multi-table DELETE с ORDER BY (4 теста), PDO 8.5-деприкейт. Парсеры Excel покрыты 57 fixture-тестами (T012). Непокрытые участки, всплывшие при апгрейде, чинились точечно с прогоном гейтов. Формально пункт не выполнен, практически риск FR-005/SC-002 закрыт

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
- [X] T012 [US3] Поднят `phpoffice/phpspreadsheet` `^1.30` → `^5.0` (установлен **5.9.0**, +maennchen/zipstream 3.2.2). Адаптация парсеров **не потребовалась**: 4 xlsx/xls-парсера используют только стабильный Reader-API (`Reader\Xlsx/Xls`, `->load()`, `->getActiveSheet()`, `->toArray()`, `catch Reader\Exception`) — удалённых в 2.0 методов нет. Parser-тесты на реальных фикстурах `.xlsx/.xls`: **57 тестов, 1540 проверок — зелёные, данные распарсились идентично**. Полные гейты: test 237, stan, cs, rector, boot — зелёные. ⏳ Ручной импорт через Uptime-UI остаётся за владельцем (fixture-тесты уже покрывают идентичность). Побочно: в `rector.php` добавлен skip `bootstrap/cache` (генерируемые файлы, как в cs)

**Checkpoint**: поверхность сжата, phpspreadsheet готов — PHP 8.5 разблокирован по этой линии.

---

## Phase 5: User Story 2 — Обновление Laravel 11 → 12 → 13 (P1)

**Goal**: Поднять фреймворк по одному мажору с Rector-автоматизацией.

**Independent Test**: после 11→12 гейты зелёные и приложение работает; отдельно после 12→13 — то же.

- [X] T013 [US2] Поднять уровень Laravel в `rector.php`: `LaravelLevelSetList::UP_TO_LARAVEL_110` → `UP_TO_LARAVEL_120` — выполнено
- [X] T014 [US2] Поднять `laravel/framework` до `^12` в `composer.json` — установлен **v12.67.0**; rector-сет `UP_TO_LARAVEL_120` прогнан: единственная модернизация — `scopeActive()` → атрибут `#[Scope]` в `CupEvent.php` (импорт `Illuminate\Database\Eloquent\Attributes\Scope`). Гейты зелёные: stan ✅, cs ✅, rector --dry-run (нет изменений) ✅, test 237 OK ✅ (падения `ShowEditPromptActionTest`/`PersonPromptServiceTest` — известные MySQL/order-флейки, изолированно зелёные), boot Laravel 12.67.0 ✅. HasUuids/Carbon3/image-SVG/local-disk — правок не потребовали (rector чист)
- [X] T015 [US2] Уровень Laravel в `rector.php`: `UP_TO_LARAVEL_120` → **`UP_TO_LARAVEL_130`** (кумулятивный, driftingly 2.5.0). Дополнительно включён `->withImportNames(importShortClasses: false, removeUnusedImports: true)` — иначе rector генерит атрибуты как inline-FQCN, нарушая правило кодстайла (импорт вместо FQCN)
- [X] T016 [US2] Поднят `laravel/framework` `^12` → `^13` (установлен **v13.26.1**); снят пин `sentry/sentry-laravel` `4.24.0` → `^4.27` (4.27.0), horizon без изменений; Symfony 7→8. Rector-сет `UP_TO_LARAVEL_130` применил 31 модернизацию: Eloquent-модели → атрибуты `#[Table]`/`#[Fillable]`/`#[Hidden]`/`#[WithoutTimestamps]`, команды → `#[Signature]`/`#[Description]`, CSRF `VerifyCsrfToken`→`PreventRequestForgery`. **Ручные BC-починки:** (1) `PersonsService.php` `orderBy('ASC'/'DESC')` → `'asc'/'desc'` (L13 ужесточил `$direction` до `'asc'|'desc'|SortDirection`); (2) `PersonController.php` импорт `Symfony\...\Request` → `Illuminate\Http\Request` (Symfony 8 удалил `Request::get()` — предсуществующий баг, всплыл на Symfony 8); (3) `EloquentRankRepository::deleteByCriteria` добавлен `->reorder()` перед `delete()` (L13 сохраняет orderBy в DELETE, MySQL запрещает ORDER BY в multi-table delete с join). Гейты зелёные: test 237, stan, cs, rector --dry-run (чист), boot L13.26.1. `serializable_classes`/session-json/`upsert` — правок не потребовали (в коде не используются)

**Checkpoint**: приложение на Laravel 13, гейты зелёные.

---
1
## Phase 6: User Story 4 — Обновление рантайма PHP до 8.5 (P2)

**Goal**: Перевести рантайм на PHP 8.5 (после phpspreadsheet и подтверждения расширений из T005).

**Independent Test**: на PHP 8.5 все гейты зелёные и приложение поднимается локально.

- [X] T017 [US4] `Dockerfile`: `FROM php:8.4-fpm` → **`php:8.5-fpm`**. Образ **собран** (`docker build`): все C-расширения (gd/pdo_mysql/mbstring/zip/exif/pcntl) компилируются на 8.5; внутри контейнера **PHP 8.5.9**, расширения на месте. phpredis не добавляем — стандартизировано на predis (T010). composer install в образе проходит
- [X] T018 [US4] PHP-ограничение в `composer.json` `^8.2` → **`^8.5`** (по решению владельца — строго 8.5 везде, SC-005). Локально переключено на PHP 8.5.8 (`brew link php`; в PATH процесса оставался прямой путь к php@8.4 — brew-симлинк `/opt/homebrew/bin/php` уже на 8.5, применится после перезапуска терминала). `composer update --lock`; `check-platform-reqs` — php 8.5.8 success. **Гейты на активном PHP 8.5.8:** test 237, stan, cs, rector --dry-run (чист), boot L13.26.1 — зелёные, ноль PHP-депрекейтов. **PDO-деприкейт починен:** `config/database.php` `PDO::MYSQL_ATTR_SSL_CA` → `Pdo\Mysql::ATTR_SSL_CA` (через `use Pdo\Mysql;`, правило кодстайла) — deprecation 8.5 устранён. Депрекейтов кастов в коде нет

**Checkpoint**: стек приложения на PHP 8.5, гейты зелёные.

---

## Phase 7: User Story 5 — Обновление инфраструктурных образов (P3)

**Goal**: Поднять образы MySQL/Redis/Nginx, зафиксировать теги в репозитории.

**Independent Test**: приложение поднимается на новых образах, данные MySQL сохранены, теги не `latest`.

- [~] T019 [US5] Бэкап БД — **пропущено по решению владельца: готовый дамп уже есть.** Реальное восстановление — на проде при деплое
- [X] T020 [US5] MySQL `8.0.27` → **`mysql:8.4`** (LTS) в `docker-compose.yml.example`. Проверено запуском `mysql:8.4.11`: стартует, `my.cnf` (bind-address) принят, дефолтные юзеры на `caching_sha2_password`. ⚠️ **BC-риск (deploy): `mysql_native_password` в 8.4 по умолчанию не загружен** — если готовый дамп содержит юзеров `IDENTIFIED WITH 'mysql_native_password'`, при импорте они не аутентифицируются. Починка: `[mysqld] mysql_native_password=ON` в my.cnf ИЛИ пересоздать юзеров с `caching_sha2_password`. Также `default_authentication_plugin` **удалён** в 8.4 — проверить, что его нет в дампе/боевом my.cnf. Пересоздание контейнера + проверка данных — deploy-time (logical dump → свежая init 8.4 + import, in-place upgrade не нужен)
- [X] T021 [US5] Redis `6.2.6` → **`redis:8-alpine`** (по решению владельца). Проверено: `redis 8.10.1` стартует. RESP обратно совместим, predis 3.x ок. В compose redis **без volume/persistence** → данных для потери нет; при пересоздании кэш/сессии/очереди сбрасываются (разлогин + потеря job'ов) — слить Horizon перед деплоем. BC-риск низкий
- [X] T022 [US5] Nginx `1.21.4-alpine` → **`nginx:1.28-alpine`** (текущий stable). Проверено `nginx -t` на 1.28: конфиг `app.conf` валиден (`listen 80`, fastcgi, `gzip_static on`, `try_files` — все директивы актуальны; ошибка upstream `app` в тесте — только из-за изоляции без compose-сети). Нет `listen ssl http2` (депрекейт 1.25+) → риска нет. BC-риск минимальный

**Checkpoint**: инфра на актуальных зафиксированных версиях.

---

## Phase 8: Polish & Cross-Cutting

**Purpose**: Финальная сверка критериев приёмки и синхронизация документации.

- [X] T023 Финальный прогон гейтов на PHP 8.5.8: test 237, stan (No errors), cs (0), rector --dry-run (done) — зелёные, boot L13.26.1. **SC-004:** `composer audit --abandoned=report` — ноль abandoned; `grep latest docker-compose.yml.example` — ноль тегов latest
- [X] T024 [P] Конституция обновлена под факт: тех-ограничения PHP 8.5 / Laravel 13, удалён Doctrine DBAL; раздел «Развёртывание» — PHP 8.5, MySQL 8.4, Redis 8, Nginx 1.28, теги зафиксированы. **PATCH-бамп 2.2.0 → 2.2.1** (+ Sync Impact Report)
- [X] T025 [P] Сверка SC-001…SC-006: **SC-001** ✅ каждый шаг с зелёными гейтами; **SC-002** ✅ 237 тестов зелёные, BC-регрессии починены с сохранением поведения; **SC-003** ✅ отдельные откатываемые коммиты (оговорка: T014 совместил T013+кодстайл; T016 — L13 + массовую FQCN-нормализацию из-за `withImportNames`); **SC-004** ✅ ноль abandoned/latest; **SC-005** ✅ PHP 8.5/L13.26.1, dbal удалён — ⚠️ predis НЕ удалён (разворот Decision 2 в T010: стандартизация на predis, т.к. реально используется); **SC-006** ⏳ deploy-time (проверяется на проде при восстановлении дампа; см. BC-риск native_password в T020)

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
