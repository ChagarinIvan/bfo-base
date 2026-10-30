# Tasks: Impressions строк протокола

**Input**: spec.md, plan.md, research.md, data-model.md, contracts/audit.md.
**Tests**: Обязательны по конституции и спецификации.

## Phase 1: Setup

- [x] T001 Проверить правила, рабочие изменения и ревью specs/050-protocol-line-impressions/spec.md.

## Phase 2: Foundation

- [x] T002 Зафиксировать ownership, автора, миграционные типы и контракт в specs/050-protocol-line-impressions/plan.md и contracts/audit.md.

## Phase 3: US1, исторический аудит

Independent test: up/down/up в отдельной базе с точным сравнением старых данных.

- [x] T003 [US1] Написать проверки backfill, пустых данных, rollback и повреждённых источников в tests/Feature/ProtocolLine/ProtocolLineImpressionsMigrationTest.php, FR-001–004/009/011.
- [x] T004 [US1] Создать database/migrations/2026_10_27_000001_add_protocol_line_impressions.php с preflight и массовым переносом, FR-001–004/009/011.

## Phase 4: US2, создание строк

Independent test: фабрика с FrozenClock, новым автором и persistence roundtrip.

- [x] T005 [US2] Расширить tests/Feature/ProtocolLine/StandardProtocolLinesFactoryTest.php проверками времени, автора, пустого ввода и повторного импорта, FR-005/006.
- [x] T006 [US2] Добавить mapping в app/Domain/ProtocolLine/ProtocolLine.php и audit defaults тестовой фабрики database/factories/Domain/ProtocolLine/ProtocolLineFactory.php, FR-001/006.
- [x] T007 [US2] Внедрить Clock и userId в app/Domain/ProtocolLine/Factory/StandardProtocolLinesFactory.php и ProtocolLinesFactory.php, передать автора из app/Domain/Event/Event.php, FR-005/010.
- [x] T008 [US2] Обновить вызовы в tests/Feature/Rank/DelayedRankActivationTest.php и tests/Feature/Cup/ElkPathCupEliteGroupsTest.php, проверить автора через импорт в tests/Feature/ProtocolLine/StandardProtocolLinesFactoryTest.php, FR-005/010.

## Phase 5: US3, переходы строки

Independent test: три перехода, сохранение created, новый updated, один event;
отклонённая операция и повторный save не меняют аудит.

- [x] T009 [US3] Добавить tests/Feature/ProtocolLine/ProtocolLineImpressionsTest.php с persistence/event/rejected-operation проверками, FR-007/008/010.
- [x] T010 [US3] Восстановить запись updated в app/Domain/ProtocolLine/ProtocolLine.php для трёх существующих переходов, FR-007/008.

## Phase 6: Verification and documentation

- [x] T011 Обновить REF-05 в .specify/memory/backend-architecture-manifest.md и добавить одну запись resources/spa/pages/updates/updates.ts; обновить каталоговый тест resources/spa/pages/updates/updates.test.ts для новой последней фичи и каталогов со spec.md.
- [x] T012 Выполнить гейты из specs/050-protocol-line-impressions/quickstart.md, проверку N+1 и запуск приложения; записать результаты в specs/050-protocol-line-impressions/validation.md.
- [x] T013 Сверить acceptance scenarios, FR-001–011 и SC-001–005 с кодом через converge; обновить статус specs/050-protocol-line-impressions/spec.md.

## Dependencies and execution order

T001 -> T002 -> T003 -> T004 -> T005 -> T006 -> T007 -> T008 -> T009 -> T010 -> T011 -> T012 -> T013.
US2 и US3 зависят от схемы US1. US3 не зависит от механизма создания US2.
Тесты пишутся до соответствующей реализации. Узкие проверки между фазами;
полные проверки выполняются в T012.

## Parallel examples

US1 migration fixtures и US2 factory fixtures можно готовить независимо,
но не запускать schema-changing tests параллельно с общей MySQL-базой.
US3 проверки и запись обновлений затрагивают разные файлы.
Фактическое выполнение последовательное для минимизации конфликтов.

## Implementation strategy

Сначала US1 как основа совместимого хранения, затем US2 и US3. Каждый этап
проверяется узко; итоговая проверка и сверка требований завершают фичу.

## Phase 7: Convergence

- [x] T014 [US2] Восстановить аудит производных строк при объединении в app/Infrastructure/Laravel/Eloquent/Event/EloquentUniteEventDataService.php и проверить его в tests/Feature/Event/UniteEventsTest.php, FR-001/005/006, US2/AC5 (partial).
- [x] T015 Повторить итоговые PHP-гейты после исправления пути объединения и записать результаты в specs/050-protocol-line-impressions/validation.md (partial).

## Completion

Все 15 задач выполнены. Итоговые результаты и coverage приведены в [validation.md](validation.md).
Финальный converge не выявил оставшейся работы.
