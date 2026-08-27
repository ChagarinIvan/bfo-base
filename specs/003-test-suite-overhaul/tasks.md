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

- [X] T002 Базовая линия зафиксирована: `time composer test` = **~193с** (real 192.93; примечательно —
  user 7.4с + sys 2.6с, т.е. ~95% времени процесс ЖДЁТ I/O MySQL по TCP). Тестов 237, предупреждений
  **3 PHPUnit Notices**. Гейты `stan`/`cs`/`rector` зелёные. (Медиана из 3 — финализируется при сравнении
  «после» в T006)

**Checkpoint**: базовая линия зафиксирована.

---

## Phase 3: User Story 1 — Быстрый прогон тестов (P1) 🎯 MVP

**Goal**: Заметно сократить полное время прогона без потери надёжности и покрытия.

**Independent Test**: `time composer test` после изменений заметно меньше базовой линии (T002); набор
зелёный и стабилен при повторных/переупорядоченных прогонах; изоляция сохранена.

- [X] T003 [US1] Диагностика выполнена. Находки: (1) прогон **I/O-bound** (95% времени ждём MySQL) →
  реальный рычаг = параллелизм; (2) `RefreshDatabase` даёт откат в serial, но параллельный **per-process
  DB-switch НЕ применяется** — запросы летят в общую `bfo_base`, коллизии на фикс-ID (`groups.101`,
  `competitions.1`); (3) `artisan test` отсутствовал (нет collision) — добавлен. Порядок по решению
  владельца: **сначала изоляция, потом параллель**
- [X] T004 [US1] **Изоляция проверена — уже корректна.** Аудит: 50 БД-тестов используют `RefreshDatabase`
  (транзакционный откат), остальные 62 (Application на моках/`make()`, Domain-юниты, Parser на фикстурах,
  Cache) **к БД не обращаются** (0 персистящих без отката). В serial засорения `bfo_base` нет — правок не
  требуется
- [~] T005 [US1] **Параллельный прогон — ОТКЛОНЁН по итогам эксперимента.** Ставили `collision`+`paratest`,
  чинили per-process DB-switch (не хватало `ParallelTestingServiceProvider` в `config/app.php` — старый
  явный список провайдеров). После починки: 18 процессов **уронили MySQL**, 4 процесса дали **0 ускорения**
  (195с ≈ базлайн — упор в единственный MySQL, не в CPU) + 67 ошибок (тесты завязаны на auto-increment ID
  и ломаются в отдельных БД). Вывод: параллель против одного MySQL не масштабируется + требует
  ID-рефакторинга тестов. Всё экспериментальное (collision/paratest/провайдер/грант) **откачено**
- [X] T006 [US1] **Ускорение: durability tuning тест-MySQL** — единственный безопасный рычаг.
  `innodb_flush_log_at_trx_commit=0` + `sync_binlog=0` → **193с → 153с (−20%)**, зелёный. Проверено и
  отвергнуто как неэффективное: `native_password` (151.8с, без разницы), гигиена фикстур (самый медленный
  тест создаёт 4 строки — резать нечего), «держать app поднятым» (буткап ~8с CPU из 158с — не он узкое
  место). Остаток ~150с — ожидание MySQL, не трассируется в один устранимый компонент обычными замерами
  (глубже — только профайлером SPX/Xdebug, вынесено). Тюнинг — **env-конфиг** (см. quickstart), не код

**Checkpoint**: US1 — безопасный выигрыш −20% (durability tuning, env). Кратное ускорение (параллель/SQLite)
заблокировано (один MySQL / MySQL-специфичный SQL / хардкод ID) — вынесено в будущий проект.

---

## Phase 4: User Story 2 — Чистый вывод: ноль предупреждений (P2)

**Goal**: Ноль PHP deprecations/notices/warnings, починка по первопричине.

**Independent Test**: `composer test -- --display-deprecations --display-notices --display-warnings` →
ноль предупреждений; ни одно не скрыто подавлением; тесты зелёные.

- [X] T007 [US2] Перечень снят (`--display-all-issues`): **ноль PHP deprecations/warnings**, ровно **3
  PHPUnit Notices** — все одного вида «No expectations were configured for the mock object … use a test
  stub instead» (PHPUnit 13): `UpdateCupServiceTest::it_fails_when_cup_not_found` (EventRepository),
  `ViewCupServiceTest::it_fails_when_cup_not_found` (EventRepository),
  `CreateOrUpdatePersonPaymentsServiceTest::it_updates_existed_payment` (PersonPaymentFactory)
- [X] T008 [US2] Починка по первопричине (НЕ подавление): в каждом из 3 тестов у неиспользуемой на этом
  пути зависимости добавлено явное `->expects($this->never())->method($this->anything())`. Это одновременно
  убирает notice И усиливает тест (фиксирует, что на пути «не найдено»/«обновление» зависимость не
  вызывается). `#[AllowMockObjectsWithoutExpectations]` НЕ использовали. Смена на `createStub` невозможна —
  в других тестах тех же файлов мок получает `->expects()`
- [X] T009 [US2] Проверка: `phpunit --display-all-issues` → **`OK (237 tests, 2257 assertions)`**, ноль
  предупреждений (assertions 2254→2257 — три новых `never()`). Гейты: stan, cs 0, rector — зелёные

**Checkpoint**: US2 — вывод чистый.

---

## Phase 5: User Story 3 — Широкий охват целевой архитектуры (P3)

**Goal**: Систематически покрыть целевой слой (доменные/Application-сервисы, сложные выборки репо,
интеграционные API/контроллеры); особо и осторожно — ranks/cups (характеризация baseline).

**Independent Test**: для выбранного модуля ключевые сценарии до были непокрыты; добавленные тесты зелёные
и падают при намеренной поломке проверяемой логики; ноль новых тестов на легаси.

- [~] T010 [P] [US3] Юнит-тесты доменных сервисов **Rank** — частично. **Добавлен** `StandardRankFactoryTest`
  (маппинг полей + дефолт finish_date = start+2 года; чистый юнит без БД). Уже были покрыты:
  `JuniorRankAgeValidator`, `StandardJuniorJuniorThirdRankChecker` (`StandardThirdRankCheckerTest`).
  **Пропущены осознанно:** `PreviousCompletedRankFiller` (очень сложный, много зависимостей — отдельный
  заход), `PreviousRanksFinishDateUpdater` (зависит от **легаси** `App\Repositories\RanksRepository` —
  тестировать домен через легаси противоречит FR-009)
- [~] T011 [P] [US3] Юнит-тесты доменных сервисов **Cup** — начато. **Добавлен** `GroupAgeTest` (лестница
  возрастов `next()`/`prev()` + границы насыщения + `toString()` — критичная логика группировки кубков).
  Уже покрыты `CupGroupFactory` (`Models/Group/CupGroupFactoryTest`), `CupCacheInvalidator`-реализация
  (`Bridge/Laravel/Cache/...`). **Осталось:** иерархия `Cup/CupType/*`, `Cup/Group/GroupMale` — следующий заход
- [~] T012 [US3] Тесты Application-сервисов **Rank** — частично. **Добавлен** `UpdateRankActivationDateServiceTest`
  (3 сценария: rank-not-found → `RankNotFound`; protocol-line-not-found → `ProtocolLineNotFound` без update;
  успех → `activateRank` применён + `update` вызван + `ViewRankDto`; моки интерфейсов + `DummyTransactional`,
  без БД). **Добавлен** `PersonRanksServiceTest` (пустой список; сборка 2 DTO с препрелоадом связей, чтобы
  batch-ассемблер `loadMissing`/`preloadProtocolLines` не бил в БД; поля DTO проверены). Уже покрыты
  `ActivateRankService`, `ViewRankService`. **Пропущены обоснованно:** `ActivePersonRankService` (зависит
  от `final readonly PreviousCompletedRankFiller` — PHPUnit не мокает final; чистый юнит без правки
  прод-кода невозможен → покрыт интеграционными Rank-контроллер-тестами), `RefillPersonRanksService`
  (тонкая обёртка над легаси `App\Services\RankService` — FR-009/FR-011)
- [X] T013 [US3] Тесты Application-сервисов **Cup** — **уже покрыты** до этой фичи: `CalculateCupEventService`,
  `UpdateCupService`, `AddCupService`, `ClearCupCacheService`, `DisableCupService`, `ViewCupService`,
  `ListCupService`, `DisableCupEventService` — все имеют тесты в `tests/Application/Service/Cup/**`
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
