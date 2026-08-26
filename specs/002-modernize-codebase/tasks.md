---
description: "Task list — модернизация кодовой базы и исправление опечатки слоя"
---

# Tasks: Модернизация кодовой базы и исправление опечатки слоя

**Input**: Design documents from `/specs/002-modernize-codebase/`

**Prerequisites**: plan.md, spec.md, research.md, quickstart.md

**Tests**: Новые тесты НЕ пишутся. Обе части — рефакторинг без изменения поведения; гарантия
неизменности — существующая регресс-сеть (~237 тестов), зелёная на каждом шаге. Прогон гейтов
(`composer test`/`stan`/`cs`/`rector`) — часть DoD каждого шага.

## Формат: `[ID] [P?] [Story] Description`

- **[P]**: можно параллельно (разные файлы, нет зависимостей). Здесь почти всё **последовательно**:
  US1 — единая операция переименования; US2 меняет общие конфиги (`rector.php`/`.php-cs-fixer.php`) и всю
  базу, поэтому шаги идут по очереди.
- **[Story]**: US1–US2 из spec.md.

## Гейт после КАЖДОГО шага (DoD, см. [quickstart.md](./quickstart.md))

`composer test` → `composer stan` → `composer cs` → `composer rector -- --dry-run` (просмотр diff) →
`php artisan --version` (boot) → отдельный коммит. Все гейты — на активном **PHP 8.5**.

## Порядок = порядок зависимостей

US1 (переименование) выполняется **до** US2 (модернизация): иначе широкий дифф модернизации смешается с
переименованием и усложнит ревью/откат (см. [research.md](./research.md) Decision 1). US1 самодостаточна
и мержится независимо (FR-013/SC-006).

---

## Phase 1: Setup (базовая линия)

**Purpose**: Подготовить окружение и зафиксировать «зелёное до».

- [X] T001 Убедиться, что работа идёт в ветке `002-modernize-codebase` (ветвь от `001-upgrade-stack`),
  рабочее дерево чистое; активен **PHP 8.5** (`php -v` → 8.5.x; при необходимости
  `export PATH="/opt/homebrew/bin:$PATH"`)

**Checkpoint**: окружение готово.

---

## Phase 2: Foundational (страховочная сеть — блокирует обе истории)

**Purpose**: Зафиксировать зелёную базовую линию, чтобы любой регресс при рефакторинге ловился гейтами.

**⚠️ CRITICAL**: без зелёной базовой линии нельзя достоверно проверить «поведение не изменилось».

- [X] T002 Зафиксировать зелёную базовую линию: `composer test` (237), `composer stan`, `composer cs`,
  `composer rector -- --dry-run` (чисто). Любой красный/шумный гейт починить ДО начала рефакторинга

**Checkpoint**: базовая линия зелёная — рефакторинг можно начинать.

---

## Phase 3: User Story 1 — Исправление опечатки `Infrastracture` → `Infrastructure` (P1) 🎯 MVP

**Goal**: Переименовать слой инфраструктуры в корректное `Infrastructure` во всём исполняемом коде и
ссылках; автозагрузка и гейты зелёные, поведение идентично.

**Independent Test**: после переименования `grep -rn "Infrastracture" app tests config database bootstrap`
= пусто; все гейты зелёные; приложение бутается; DI-биндинги резолвятся.

- [X] T003 [US1] Замерить область для проверки полноты (SC-001): зафиксировать
  `grep -rc "namespace App\\Infrastracture" app` (ожидаемо 16) и список файлов
  `grep -rl "Infrastracture" app tests config database bootstrap` (~38) — эталон «ожидаемый ноль после»
- [X] T004 [US1] Переименовать каталог с сохранением истории: `git mv app/Infrastracture app/Infrastructure`
- [X] T005 [US1] Заменить `App\Infrastracture` → `App\Infrastructure` во всех `.php`: namespace-объявления
  в `app/Infrastructure/**`, `use`-импорты и `::class` по всему `app/` (особенно DI-биндинги в
  `app/Bridge/Laravel/Provider/*`), ссылки из интерфейсов `app/Domain/*`, тесты `tests/**`, конфиги `config/**`
- [X] T006 [US1] Перегенерировать автозагрузчик: `composer dump-autoload`
- [X] T007 [US1] Проверить SC-001: `grep -rn "Infrastracture" app tests config database bootstrap` → ноль
  совпадений (исторические артефакты в `specs/001-*` не входят в критерий)
- [X] T008 [US1] Гейты (`test`/`stan`/`cs`/`rector -- --dry-run`) + `php artisan --version` (boot);
  отдельный коммит `refactor(US1): infrastracture → infrastructure`

**Checkpoint**: US1 завершена — опечатка исправлена, стек зелёный, можно мержить независимо.

---

## Phase 4: User Story 2 — Модернизация под PHP 8.5 / Laravel 13 как стандарт (P2)

**Goal**: Применить **максимальный** набор современных конструкций PHP 8.5 (полный PHP-сет + строгие
наборы) и идиом Laravel 13 к `app/` **и** `tests/`, закрепив как постоянный стандарт в
`rector.php`/`.php-cs-fixer.php`. Объём — максимум (уточнение 2026-08-26); небезопасное отсеивается гейтами.

**Independent Test**: `composer rector -- --dry-run` чисто (идемпотентность), `cs` 0, `stan` без ошибок,
все 237 тестов зелёные; внесённая заведомо устаревшая конструкция отмечается инструментом.

- [X] T009 [US2] Добавить `->withPhpSets()` в `rector.php` (полный PHP-сет из composer floor `^8.5`);
  `composer rector`; просмотреть дифф (только форма, не поведение); гейты; коммит
- [X] T010 [US2] Строгие семантические группы `instanceOf` + `if` в `->withPreparedSets(...)`
  (⚠️ `strictBooleans` в rector 2.6.3 отсутствует — использованы реальные группы API). 22 файла:
  `ObjectExplicitBoolCompareRector` (truthy → явные сравнения), `BinaryOpNullableToInstanceofRector`,
  `ExplicitBoolCompareRector`. Поведение эквивалентно (все 237 тестов, включая 57 fixture-парсеров,
  зелёные). `codingStyle` намеренно НЕ включён (стиль — за php-cs-fixer, Decision 4). Гейты: stan, cs 0,
  rector идемпотентен, boot — зелёные
- [~] T011 [US2] Группа `naming` — **ОТКЛОНЕНА гейтом** (по задаче «при вреде откатить группу»). Дала
  269–321 файл ренеймов (params/vars/properties под тип) И **сломала код**: рассинхрон `@var`-докблоков +
  5× «Access to undefined property» в `ExportPersonsCommand`, битый `@var` в `MasterCupType` — 8 ошибок
  stan. Даже с исключённым `RenamePropertyToMatchTypeRector` (риск Blade/сериализации) остаток ломал типы.
  Откачено полностью
- [~] T012 [US2] Группа `namedArgs` — **ОТКЛОНЕНА гейтом**. 90 файлов (`AddNameToBooleanArgumentRector`/
  `AddNameToNullArgumentRector`: `foo(true)` → `foo(active: true)` + `ExplicitAttributeNamedArgsRector`).
  Проставила именованные аргументы там, где они **запрещены** (вариадики/сигнатуры без поддержки) → 29
  ошибок stan «named argument … not allowed». Откачено полностью. (Безопасную часть —
  `ExplicitAttributeNamedArgsRector` для атрибутов — можно при желании включить отдельно с skip двух
  ломающих правил; вынесено из объёма)
- [ ] T013 [US2] Рассмотреть уровневые наборы (`->withTypeCoverageLevel(...)`,
  `->withDeadCodeLevel(...)`, `->withCodeQualityLevel(...)`) в `rector.php` — включать по одному, если
  дают полезный дифф без ломки поведения; `composer rector`; гейты; коммит на каждую полезную группу
- [ ] T014 [US2] Усилить стилевые/migration-правила в `.php-cs-fixer.php` (согласовать с принципом VI
  «импорт вместо FQCN», без конфликта с rector — повторный прогон идемпотентен); `composer cs-fix`;
  гейты; коммит
- [ ] T015 [US2] Убедиться, что модернизация покрыла и `app/`, и `tests/` (пути rector уже включают оба);
  при пропусках — догнать прогоном; гейты
- [ ] T016 [US2] Проверить идемпотентность (SC-004): повторный `composer rector -- --dry-run` →
  «Rector is done!» без изменений; `composer cs` → 0 файлов к правке
- [ ] T017 [US2] Проверить закрепление стандарта (SC-005): внести в тестовый файл заведомо устаревшую
  конструкцию, убедиться, что `composer rector -- --dry-run` / `composer cs` её отмечают (или
  автоприводят), затем откатить пробную правку

**Checkpoint**: база приведена к современному виду, стандарт закреплён в инструментах и идемпотентен.

---

## Phase 5: Polish & Cross-Cutting

**Purpose**: Финальная сверка и фиксация стандарта в документации.

- [ ] T018 [P] Отразить закреплённый стандарт модернизации комментариями в `rector.php` и
  `.php-cs-fixer.php` (какие наборы включены и почему); при необходимости — короткая заметка в
  `CLAUDE.md`/конституции
- [ ] T019 Финальный полный прогон гейтов на PHP 8.5: `test` (237), `stan`, `cs`, `rector -- --dry-run`
  (чисто), boot
- [ ] T020 [P] Свериться со всеми SC-001…SC-006 из spec.md; отметить выполненные критерии; прогнать
  сценарии из [quickstart.md](./quickstart.md)

---

## Dependencies (порядок завершения)

```text
Phase 1 (Setup) → Phase 2 (Foundational, зелёная база)
   → Phase 3 US1 (rename Infrastracture → Infrastructure)   [MVP, независимо мержится]
   → Phase 4 US2 (модернизация как стандарт: PhpSets → строгие группы → уровневые → cs-fixer)
   → Phase 5 (Polish: документация стандарта, финальная сверка SC)
```

Внутри Phase 3: T003 → T004 → T005 → T006 → T007 → T008 (последовательно, единая операция rename).
Внутри Phase 4: T009 → T010 → … → T017 (последовательно, общий `rector.php`; по одной группе правил на шаг).
Ключевая зависимость: **US2 после US1** — чтобы дифф модернизации не смешивался с переименованием.

## Параллельные возможности

Почти всё последовательно (единая rename-операция в US1; общий `rector.php` в US2). Реально параллельны
только финальные T018 и T020 (`[P]`, разные файлы/артефакты, без прогонов, меняющих общий код).

## Implementation Strategy

- **MVP** = Phase 3 (US1): исправление опечатки слоя. Точечно, детерминированно, мержится само по себе.
- Дальше US2 — строго по одной группе правил модернизации: включили группу → `composer rector` →
  просмотр диффа (только форма) → небезопасное в `withSkip` + откат → зелёные гейты → отдельный коммит.
- Критерий завершения US2 — идемпотентность (`rector --dry-run` чист) + закреплённый в конфиге стандарт
  (SC-004/SC-005), а не разовый прогон.
