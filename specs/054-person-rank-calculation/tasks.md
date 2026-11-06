# Tasks: Person rank calculation metadata

Input: spec, plan, research, data model, contracts. Regression-first обязательно.

## Phase 1: Setup

- [x] T001 Уточнить auth/PersonView/UI, выполнить specify review в specs/054-person-rank-calculation/spec.md
- [x] T002 Исследовать все callers и провести plan review в specs/054-person-rank-calculation/plan.md и research.md

## Phase 2: Foundation

- [x] T003 Выполнить read-only analyze и сохранить результат отдельным шагом в specs/054-person-rank-calculation/analysis.md

## Phase 3: US1, последний успешный расчёт, P1

Independent test: null defaults, success, same-rank, unrelated update, rollback, up/down.

- [x] T004 [US1] Добавить failing metadata/rollback tests в tests/Feature/Person/PersonRankCalculationTest.php и migration test в PersonRankCalculationMigrationTest.php
- [x] T005 [US1] Добавить enum и Person mutation/casts в app/Domain/Person/RankCalculationReason.php и Person.php, миграцию database/migrations/2026_11_01_000001_add_person_rank_calculation_metadata.php
- [x] T006 [US1] Передать reason из commands в app/Application/Service/Person/RebuildPersonRanksService.php и app/Domain/Person/StandardEventPersonRankUpdater.php

## Phase 4: US2, источник запуска, P1

Independent test: все действующие reasons, все production paths и очередь, execution time.

- [x] T007 [US2] Расширить propagation regressions в tests/Application/Service/Rank/RebuildPersonRanksServiceTest.php, tests/Bridge/Laravel и tests/Feature/{Person,Rank,Event,Api/V1/PersonRankHistory,ProtocolLine}/
- [x] T008 [US2] Передать enum по всем callers app/Application/{Service,Handler} и app/Bridge/Laravel/{Jobs,Console/Commands,Http/Controllers/Api/V1/Person}
- [x] T009 [US2] Проверить narrow red/green и записать specs/054-person-rank-calculation/validation.md

## Phase 5: US3, авторизованный PersonView, P2

Independent test: auth nullable поля в том же PersonView, guest ключи отсутствуют.

- [x] T010 [US3] Добавить failing API tests в tests/Feature/Api/V1/Person/PersonRankCalculationViewTest.php
- [x] T011 [US3] Расширить app/Application/Dto/Person/{PersonAssembler,ViewPersonDto}.php и resources/spa/api/types.ts

## Phase 6: Polish

- [x] T012 Добавить updates запись и latest expectation в resources/spa/pages/updates/updates{,.test}.ts
- [x] T013 Пройти CS/STAN/Rector, полный PHP/frontend, HTTP smoke и отсутствие новых queries, записать specs/054-person-rank-calculation/validation.md
- [x] T014 Сверить критерии и выполнить converge по specs/054-person-rank-calculation/tasks.md

## Dependencies and strategy

Setup -> Foundation -> US1 -> US2 -> US3 -> Polish. US1 обеспечивает хранения,
US2 покрывает все источники, US3 обеспечивает чтение. Минимальный increment US1,
полный пользовательский запрос включает все фазы. Tests сначала fail до реализации.
Независимое чтение callers и тестов US2 может выполняться параллельно; API tests
и migration tests затрагивают разные файлы, но требуют готового schema/model при green.
Полные suites один раз в конце, узкие tests между фазами.

## Уточнение причин 2026-11-06

- [x] T015 Разделить причины обработки результатов, очистки и смены спортсмена; сохранить старый protocol.
- [x] T016 Обновить API-контракт, артефакты и существующую updates запись.
- [x] T017 Проверить регрессии трёх callers и совместимость прежних значений, запустить quality gates.
