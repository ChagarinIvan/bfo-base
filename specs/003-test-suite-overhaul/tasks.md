---
description: "Task list — переработка тестового набора (скорость, чистота, покрытие)"
---

# Tasks: Переработка тестового набора (скорость, чистота, покрытие)

**Input**: Design documents from `/specs/003-test-suite-overhaul/`

**Prerequisites**: plan.md, spec.md, research.md, quickstart.md

**Tests**: Эта фича **сама по себе — про тесты**: задачи US3 создают тест-файлы, US1/US2 — инфраструктура
и чистота прогона. Поведение приложения не меняется. Гейты (`composer test`/`stan`/`cs`/`rector`) —
часть DoD каждого шага, все на PHP 8.5.

## Формат: `[ID] [P?] [Story] Description`

- **[P]**: можно параллельно (разные файлы, нет зависимостей).
- **[Story]**: US1–US3 из spec.md.

## Порядок = порядок зависимостей

US1 (скорость) → US2 (чистота) → US3 (покрытие): быстрый и чистый прогон — база, на которой удобно и
быстро расширять покрытие. Истории независимо тестируемы, но такой порядок даёт лучший фидбэк-цикл.

## Гейт после КАЖДОГО шага (DoD, см. [quickstart.md](./quickstart.md))

`composer test` (зелёный, число тестов не уменьшилось) → `composer stan` → `composer cs` →
`composer rector -- --dry-run` → отдельный откатываемый коммит.

---

## Phase 1: Setup

**Purpose**: Подготовить окружение.

- [ ] T001 Убедиться, что работа в ветке `003-test-suite-overhaul` (от `002-modernize-codebase`), рабочее
  дерево чистое; активен PHP 8.5 (`php -v` → 8.5.x; при необходимости `export PATH="/opt/homebrew/bin:$PATH"`)

**Checkpoint**: окружение готово.

---

## Phase 2: Foundational (базовая линия — блокирует US1 и US2)

**Purpose**: Зафиксировать «до» для скорости и чистоты.

**⚠️ CRITICAL**: без базовой линии нельзя доказать ускорение (US1) и собрать список предупреждений (US2).

- [ ] T002 Зафиксировать базовую линию по протоколу SC-001: **медиана из 3 прогонов** `time composer test`
  в одинаковом окружении (записать), полный снимок предупреждений
  `composer test -- --display-deprecations --display-notices --display-warnings` (сохранить список), и
  зелёные `stan`/`cs`/`rector -- --dry-run`

**Checkpoint**: базовая линия зафиксирована.

---

## Phase 3: User Story 1 — Быстрый прогон тестов (P1) 🎯 MVP

**Goal**: Заметно сократить полное время прогона без потери надёжности и покрытия.

**Independent Test**: `time composer test` после изменений заметно меньше базовой линии (T002); набор
зелёный и стабилен при повторных/переупорядоченных прогонах; изоляция сохранена.

- [ ] T003 [US1] Диагностика инфраструктуры БД: подтвердить, что `RefreshDatabase` даёт транзакционный
  откат и `migrate:fresh` идёт один раз за прогон; выявить точки лишней персистенции/обращений к БД
  (профиль самых медленных тестов) — зафиксировать находки
- [ ] T004 [US1] Включить параллельный прогон: **установить `brianium/paratest` (версия под PHPUnit 13)
  через `composer require --dev` + обновить `composer.lock`** (без него `--parallel` не запустится на
  чистом checkout); настроить `php artisan test --parallel` с отдельной тест-БД на процесс
  (`.env.testing`/`phpunit.xml`/`composer.json` скрипт `test`); убедиться в отсутствии гонок и падений от
  общей БД
- [ ] T005 [US1] Гигиена фикстур: там, где БД не нужна — `make()`/стабы вместо `create()`; сократить объём
  персистируемых строк/связей в `database/factories/*` и в тестах-«тяжеловесах», выявленных в T003
- [ ] T006 [US1] Повторный замер `time composer test`: заметно меньше базовой линии; прогон зелёный и
  стабильный (нет новых флейков от порядка/остаточных данных); полные гейты; коммит

**Checkpoint**: US1 — прогон ускорен, стабилен, изоляция сохранена.

---

## Phase 4: User Story 2 — Чистый вывод: ноль предупреждений (P2)

**Goal**: Ноль PHP deprecations/notices/warnings, починка по первопричине.

**Independent Test**: `composer test -- --display-deprecations --display-notices --display-warnings` →
ноль предупреждений; ни одно не скрыто подавлением; тесты зелёные.

- [ ] T007 [US2] Собрать полный перечень предупреждений из снимка T002 (deprecations/notices/warnings),
  сгруппировать по первопричине (устаревший тестовый вызов / прикладной вызов / сторонний пакет)
- [ ] T008 [US2] Устранить каждое предупреждение правкой первопричины (адаптация под PHP 8.5 / PHPUnit 13 /
  Laravel 13) в `tests/**` и, при необходимости, заменой устаревшего вызова в `app/**` без смены поведения;
  БЕЗ подавления/baseline/игнора
- [ ] T009 [US2] Проверка: прогон с `--display-*` → ноль предупреждений; полные гейты; коммит

**Checkpoint**: US2 — вывод чистый.

---

## Phase 5: User Story 3 — Широкий охват целевой архитектуры (P3)

**Goal**: Систематически покрыть целевой слой (доменные/Application-сервисы, сложные выборки репо,
интеграционные API/контроллеры); особо и осторожно — ranks/cups (характеризация baseline).

**Independent Test**: для выбранного модуля ключевые сценарии до были непокрыты; добавленные тесты зелёные
и падают при намеренной поломке проверяемой логики; ноль новых тестов на легаси.

- [ ] T010 [P] [US3] Юнит-тесты доменных сервисов **Rank** (только классы с логикой; интерфейс
  `JuniorThirdRankChecker` НЕ тестируем — тестируем его реализацию): `JuniorRankAgeValidator`,
  `StandardJuniorJuniorThirdRankChecker`, `PreviousCompletedRankFiller`, `PreviousRanksFinishDateUpdater`,
  `Factory/StandardRankFactory` в `tests/Domain/Rank/**` (чистая логика, без БД)
- [ ] T011 [P] [US3] Юнит-тесты доменных сервисов **Cup** (классы с логикой; интерфейс
  `CupCacheInvalidator` НЕ тестируем — его Laravel-реализация проверяется в Bridge/Infrastructure):
  иерархия `Cup/CupType/*` (Ski/Bike/Sprint/Master/NewMaster/Youth/NewYouth/Elite/Junior/ElkPath),
  `Cup/Group/*` (`CupGroupFactory`, `GroupAge`, `GroupMale`) в `tests/Domain/Cup/**` (критичный раздел —
  покрыть тщательно)
- [ ] T012 [US3] Тесты Application-сервисов **Rank** (моки интерфейсов): `ActivePersonRankService`,
  `RefillPersonRanksService`, `UpdateRankActivationDateService`, `PersonRanksService`, `ActivateRankService`
  в `tests/Application/Service/Rank/**`
- [ ] T013 [US3] Тесты Application-сервисов **Cup**: `CalculateCupEventService`, `UpdateCupService`,
  `AddCupService`, `ClearCupCacheService`, `DisableCupService` в `tests/Application/Service/Cup/**`
- [ ] T014 [P] [US3] Тесты прочих непокрытых Application-сервисов целевого слоя: PersonPrompt (`Add`/`Update`/
  `Delete`), PersonPayment (`ListPersonsPayments`, `CreateOrUpdate`), CupEvent (`ListCupEvent`), Event
  (`DownloadEventProtocol`) в `tests/Application/Service/**`
- [ ] T015 [US3] Тесты сложных выборок целевых Eloquent-репозиториев на репрезентативных данных:
  `EloquentRankRepository` (`buildQuery` с join/sorting/критериями), `EloquentPersonRepository::byCriteria()`
  / `oneByCriteria()` (реальные ветви фильтрации), `EloquentCupRepository` в `tests/Infrastructure/**`.
  ⚠️ Поиск с `CONCAT` — это легаси `app/Services/PersonsService.php`, тесты на него ЗАПРЕЩЕНЫ (FR-009),
  в объём НЕ входит
- [ ] T016 [US3] Интеграционные request/API-тесты контроллеров разрядов и кубков (и ключевых эндпоинтов),
  частично покрывающие легаси через публичное поведение, в `tests/Bridge/Laravel/Http/**`
- [ ] T017 [US3] Характеризация ranks/cups (SC-004): тесты фиксируют текущее поведение как baseline;
  проверить осмысленность — намеренно сломать логику ranks/cups, убедиться, что тесты падают, откатить пробу
- [ ] T018 [US3] Контроль границ (FR-009/SC-005): ноль новых тестов на легаси `app/Repositories`/
  `app/Services`; полные гейты; коммит

**Checkpoint**: US3 — целевой слой покрыт широко, ranks/cups зафиксированы.

---

## Phase 6: Polish & Cross-Cutting

**Purpose**: Финальная сверка.

- [ ] T019 Финальный прогон на PHP 8.5: `time composer test` (сравнение с базовой линией), `--display-*`
  (ноль предупреждений), `stan`/`cs`/`rector` — зелёные
- [ ] T020 Сверка SC-001…SC-006; отметить критерии; прогнать сценарии из [quickstart.md](./quickstart.md)
  (зависит от T019 и всех предыдущих фаз — НЕ параллельна)

---

## Dependencies (порядок завершения)

```text
Phase 1 (Setup) → Phase 2 (Foundational, базовая линия)
   → Phase 3 US1 (скорость: диагностика → параллелизм → гигиена фикстур → замер)   [MVP]
   → Phase 4 US2 (чистота: перечень → починка первопричин → ноль предупреждений)
   → Phase 5 US3 (покрытие: домен Rank/Cup → Application Rank/Cup/прочее → репо → интеграционные → характеризация)
   → Phase 6 (Polish)
```

Внутри US3: T010/T011 (домен, разные файлы) — параллельны; T012→T013 (могут делить фикстуры Rank/Cup —
последовательно осторожно); T014 [P]; T015/T016 — независимы; T017 опирается на наличие тестов ranks/cups;
T018 — контроль в конце.

## Параллельные возможности

- US3 доменные юнит-тесты Rank (T010) и Cup (T011) — разные каталоги, параллельны.
- T014 (прочие Application-сервисы) — независимые файлы.
- T020 — финальная сверка SC: **зависит** от T019 и всех фаз, выполняется последовательно (не [P]).
- US1/US2/US3 как истории независимы, но рекомендован порядок P1→P2→P3 ради фидбэк-цикла.

## Implementation Strategy

- **MVP** = Phase 3 (US1): ускорение прогона — ежедневная ценность, независимо мержится.
- Далее US2 (чистая база) → US3 (широкое покрытие целевого слоя). ranks/cups — приоритетно и осторожно
  (характеризация baseline, T017).
- Каждый шаг — зелёные гейты + отдельный откатываемый коммит; поведение приложения неизменно.
