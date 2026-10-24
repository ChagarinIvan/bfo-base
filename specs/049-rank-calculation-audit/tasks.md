# Tasks: корректность расчёта и аудит разрядов

**Input**: spec.md, plan.md, research.md, data-model.md, contracts/rank-audit.md.

## Phase 1: Setup

- [x] T001 Проверить существующие правила и составить карту входов в specs/049-rank-calculation-audit/research.md (FR-001, FR-002).
- [x] T002 Проверить ignore-файлы и подготовить миграцию database/migrations/*rank_audit* (FR-008, FR-013, FR-016).

## Phase 2: Foundation

- [x] T003 Добавить чистые RankAuditTrigger, RankAuditEntry, типизированные снимки и repository port в app/Domain/Person/Audit/ (FR-008–FR-013).
- [x] T004 Добавить append-only хранение и indexed Slice в app/Infrastructure/Laravel/Eloquent/Person/RankAudit/ и binding в app/Bridge/Laravel/Provider/Rank/RankProvider.php (FR-008–FR-016).

## Phase 3: US1, корректный расчёт

Независимая проверка: будущая активация, конец календарного дня, иной порядок выполнения/активации и повторный расчёт.

- [x] T005 [US1] Добавить regression-first тесты tests/Domain/Person/RankCalculationRegressionTest.php с чистыми входами (FR-002–FR-004, FR-018).
- [x] T006 [US1] Исправить сортировку, будущие активации и временные границы в app/Domain/Person/RankCalculator.php (FR-002–FR-004).
- [x] T007 [US1] Свести app/Domain/Person/StandardEventPersonRankUpdater.php к общему audited Application пути через доменный PersonRanksUpdater, обеспечить no-op и rollback в app/Application/Service/Person/RebuildPersonRanksService.php (FR-001, FR-003, FR-008–FR-013).

## Phase 4: US2, равное/высшее подтверждение

Независимая проверка: каждый подтверждаемый разряд после равного/высшего, истёкшее основание, отсутствие/отзыв основания и ручная дата.

- [x] T008 [US2] Добавить чистые policy tests и tests/Feature/Rank/RankActivationTest.php (FR-005–FR-007, FR-018).
- [x] T009 [US2] Добавить общую app/Domain/Person/RankActivationPolicy.php по фактам без признака применимости в ProtocolLine; исключить SQL-запись вычисляемой активации при идентификации (FR-005–FR-007).
- [x] T010 [US2] Обеспечить audit Impression изменяемых методов app/Domain/ProtocolLine/ProtocolLine.php и проверить сохранение ручных дат (FR-006, FR-012).

## Phase 5: US3, журнал

Независимая проверка: увидеть trigger, created и различия двух состояний в карточке; проверить ошибки, авторизацию и отсутствие новых записей при no-op.

- [x] T011 [US3] Добавить persistence/rollback/dedup tests в tests/Feature/Rank/RankAuditTest.php (FR-008–FR-013, FR-016, FR-018).
- [x] T012 [US3] Передать enum триггера через app/Application/Handler/Rank/ProtocolLineRankActivatedHandler.php, rank jobs, daily/refill, CleanupEventResultsService и SetPersonToProtocolLinesService (FR-001, FR-008–FR-011).
- [x] T013 [US3] Добавить API regression tests в tests/Feature/Api/V1/PersonRankAudit/RankAuditApiTest.php (FR-014–FR-016, FR-018).
- [x] T014 [US3] Добавить read commands/services/assembler/DTO в app/Application/Service/Person/RankAudit/ и app/Application/Dto/Person/RankAudit/; actions и authenticated routes (FR-014–FR-016).
- [x] T015 [P] [US3] Добавить tests resources/spa/pages/persons/PersonRankAudit.test.ts (FR-014, FR-015, FR-018).
- [x] T016 [US3] Добавить resources/spa/api/personRankAudit.ts, PersonRankAudit.vue, подключение PersonRanksPage.vue и белорусские тексты resources/lang/by.json (FR-014–FR-016).

## Phase 6: US4, коррекция

Независимая проверка: preview ничего не меняет, apply соответствует preview и повтор идемпотентен.

- [x] T017 [US4] Добавить tests/Feature/Rank/CorrectPersonRanksTest.php (FR-017, FR-018).
- [x] T018 [US4] Добавить preview/apply command/service и Bridge CLI в app/Application/Service/Person/CorrectPersonRanksService.php и app/Bridge/Laravel/Console/Commands/CorrectPersonRanksCommand.php (FR-017).

## Phase 7: Polish

- [x] T019 Адаптировать изменённые collaborator tests и выполнить узкие backend/SPA проверки в tests/ и resources/spa/ (FR-018).
- [x] T020 Выполнить полный quality gate из specs/049-rank-calculation-audit/quickstart.md, проверить запуск приложения и bounded/indexed queries (FR-018, SC-001–SC-007).
- [x] T021 Сверить acceptance scenarios и обновить итог исследования/статус в specs/049-rank-calculation-audit/research.md и spec.md (FR-001, FR-002, FR-018).

## Dependencies and execution

T001 → T002 → T003 → T004. US1 после Foundation; US2 после исправления расчёта; US3 интегрирует общую запись и входы; US4 использует расчёт и аудит. Тесты каждого сценария пишутся перед реализацией. T015 может выполняться независимо от backend API tests T013 при готовом контракте; интеграция T016 после T014. Полные проверки только в T020.

## Implementation strategy

Первый проверяемый результат: исправленный расчёт US1. Затем политика I и аудит, защищённый просмотр, управляемая историческая коррекция. Все пользовательские истории обязательны для завершения 049.

## Уточнение пользователя: общее правило без флага

- [x] T022 [US2] Проверить матрицу равного/высшего подтверждения для I/КМС/МС/МСМК в tests/Domain/Person/RankActivationPolicyTest.php и tests/Feature/Rank/RankActivationTest.php.
- [x] T023 [US2] Заменить FirstRankActivationPolicy на RankActivationPolicy; удалить флаг, порт коррекции флага и SQL-автоактивацию из app/Domain/ProtocolLine/ProtocolLineOperations.php и app/Infrastructure/Laravel/Eloquent/ProtocolLine/EloquentProtocolLineOperations.php.
- [x] T024 [US2] Согласовать historical preview/apply, аудит, миграцию, документацию и существующие тесты с единым правилом; выполнить узкие и финальные проверки.

## Полный аудит ProtocolLine

- [x] T025 Добавить created вместе с updated в app/Domain/ProtocolLine/ProtocolLine.php, схему и nullable cast; передавать Impression текущего импорта через ProtocolLinesFactory, покрыть сохранение и неизменность created в tests/Feature/ProtocolLine/ProtocolLineImpressionsTest.php и связанных проверках импорта.

## Уточнение структуры и архитектуры аудита

- [x] T026 Заменить словарь в Domain RankAuditEntry типизированными trigger/created/before/after; JSON оставить в RankSnapshotMapper и транспортном assembler.
- [x] T027 Вернуть RebuildPersonRanksService к оркестрации, удалить PersonRanksRebuilder port и вынести общий расчёт/запись аудита в Domain PersonRanksUpdater.
- [x] T028 Сократить схему, API и фронт до trigger, created и двух состояний; удалить summary, дублирующие метаданные и no-op записи. Проверить все сценарии и финальные гейты.
