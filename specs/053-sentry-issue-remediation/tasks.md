# Tasks: Sentry issue remediation

Input: spec.md, plan.md, research.md, data-model.md, contracts/remediation.md.
Tests обязательны, regression-first. Фазы одобрены запросом полного прогона.

## Phase 1: Setup

- [x] T001 Восстановить OAuth и записать доступ в specs/053-sentry-issue-remediation/access-status.md
- [x] T002 Прочитать правила и провести spec review в specs/053-sentry-issue-remediation/spec.md

## Phase 2: Foundation

- [x] T003 Завершить research и design review в specs/053-sentry-issue-remediation/plan.md и research.md
- [x] T004 Выполнить consistency analyze spec/plan/tasks, сохранить отчёт в specs/053-sentry-issue-remediation/analysis.md после read-only фазы

## Phase 3: US1, полный реестр, P1

Goal: каждому issue соответствует evidence, confidence и решение.
Independent test: 9 уникальных IDs, все events прочитаны, фильтр и период записаны.

- [x] T005 [US1] Получить 90d snapshot и события всех issues в specs/053-sentry-issue-remediation/issue-inventory.md
- [x] T006 [US1] Связать причины с текущим кодом и ограничить неизвестные production факты в specs/053-sentry-issue-remediation/research.md

## Phase 4: US2, регрессии и исправления, P1

Goal: startup HTTP, group lookup и повторный cleanup безопасны.
Independent test: новые регрессии сначала fail, затем pass; исторические контракты pass.

- [x] T007 [P] [US2] Добавить failing startup HTTP regression в tests/Feature/Infrastructure/ProductionPhpResponseTest.php
- [x] T008 [P] [US2] Добавить failing batch/cache regressions в tests/Feature/ProtocolLine/StandardProtocolLinesFactoryTest.php
- [x] T009 [P] [US2] Добавить failing repeated cleanup и unexpected failure tests в tests/Feature/ProtocolLine/ProtocolLinePersonAssignedHandlerTest.php
- [x] T010 [US2] Исправить startup output в enviroment/php/99-security.ini, сохранив error logging
- [x] T011 [US2] Загрузить группы одним запросом и сбрасывать кэши в app/Domain/ProtocolLine/Factory/StandardProtocolLinesFactory.php
- [x] T012 [US2] Обработать только PersonNotFound cleanup в app/Application/Handler/ProtocolLine/ProtocolLinePersonAssignedHandler.php
- [x] T013 [US2] Проверить defaults, schema/ranks и DI существующими tests/Feature/Rank/DelayedRankActivationTest.php, tests/Feature/Event/UniteEventsTest.php и tests/Application/Handler/Event/ParseEventProtocolHandlerTest.php
- [x] T014 [US2] Проверить узкие регрессии и сохранить red/green evidence в specs/053-sentry-issue-remediation/validation.md

## Phase 5: US3, полнота результата, P2

Goal: reviewable spec-kit и честный результат каждой issue.
Independent test: gates pass и 9/9 outcomes с deployment limitations.

- [x] T015 [US3] Добавить одну пользовательскую запись в resources/spa/pages/updates/updates.ts и latest expectation в updates.test.ts
- [x] T016 [US3] Выполнить CS/STAN/Rector/PHP/frontend gates и app smoke, записать specs/053-sentry-issue-remediation/validation.md
- [x] T017 [US3] Сверить 9/9 outcomes и production actions в specs/053-sentry-issue-remediation/issue-inventory.md и quickstart.md

## Phase 6: Polish

- [x] T018 Проверить secrets, git diff --check и выполнить converge по specs/053-sentry-issue-remediation/tasks.md

## Dependencies and execution

Setup -> Foundation -> US1 -> US2 -> US3 -> Polish. T007/8/9 независимы по файлам,
каждая регрессия должна упасть до соответствующего T010/11/12. T013 независим от
изменений factory/handler, но запускать только узкие тесты. Full suites один раз T016.
US1 MVP полезен отдельно, запрос пользователя охватывает все фазы.

Parallel examples: US1 чтение разных issue events; US2 три отдельных test files;
US3 CS и STAN после окончания правок. Исполнение допускает последовательный режим.
