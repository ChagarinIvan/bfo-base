# Validation

Локальный PHP 8.5.10, без Docker. MySQL bfo_base_test; migration roundtrip в
изолированной SQLite memory connection, production не затрагивается.

## Regression-first

До реализации: 4 новых tests, 2 fail. Нет timestamp после успешного rebuild и нет
rankCalculatedAt в authenticated PersonView. Guest и rollback исходного state pass.
Отдельно 5 existing tests с новыми assertions: protocol/daily/refill fail с null.

После реализации первый scoped набор: 15 tests / 138 assertions PASS, включая
stage updater, daily/refill, cleanup, API и rollback. Queue/migration/mocks набор:
15 tests / 102 assertions PASS. Дополнительные проверки охватывают collector failure,
manual activation, старые jobs, все четыре enum значения и execution time.
После Rector уточнён mock argument matching без callback, чтобы он сохранял bool
контракт PHPUnit. Финальный focused набор: 13 tests / 72 assertions PASS.

## Coverage of sources

| Source | Reason | Evidence |
| --- | --- | --- |
| API rebuild | manual | PersonRankCalculationViewTest, request reason не влияет |
| Manual rank activation/date | manual | PersonRankCalculationTest, direct after-commit handler |
| Protocol rank stage | protocol | StandardEventPersonRankUpdaterTest |
| Cleanup/replacement | protocol | ReplaceEventProtocolTest, affected и removed Person |
| Reassignment, new/old Person | protocol | AssignPersonOnProtocolLinesServiceTest, typed mutation argument; shared service passes same reason for both branches |
| Daily command/selection | daily | RebuildExpiredPersonRanksTest, command + service + unselected Person |
| Bulk refill command | refill | RefillPersonRanksCommandTest |
| Queue bulk/atomic | selected reason | RebuildPersonRanksJobTest; PersonRankCalculationTest enum cases + serialized old job |

## Invariants

- Same rank advances calculation time, unrelated info edit preserves it.
- Collector failure and repository failure after save retain committed old metadata.
- Migration up/down/up retains current ranks, old/new rows default null.
- Auth detail/list include metadata beside rankId, guest omits populated fields.
- Existing commands, locks, transactions, updated Impression, events and soft disable unchanged.
- Scalars are read from loaded Person, no new relation or query. Existing persons N+1
  regression remains part of full suite.
- Two scoped Rector exclusions preserve property defaults needed by old serialized jobs;
  they do not suppress PHPStan diagnostics or change repository/domain contracts.

## Final gates

| Gate | Result |
| --- | --- |
| composer cs | PASS, 0 files need changes |
| composer stan | PASS, no errors |
| composer rector -- --dry-run | PASS, no changes |
| composer test, bfo_base_test | PASS, 720 tests, 5498 assertions, 15 existing PHPUnit notices |
| npm run ci | PASS, lint/Prettier, typecheck, 85 files/283 tests, Vite build |
| Application startup | PASS, native HTTP subprocess ProductionPhpResponseTest in full suite |
| Persons N+1 | PASS, existing query-count regression; metadata adds no relation/query |
| git diff --check | PASS |

Полный PHP и frontend запущены по одному разу после завершения реализации.
Прежнее предупреждение Vite о размере chunk не блокирует сборку.
Изменения не выложены, migration на production не выполнялась. Перед новым
worker выполнить migrate --force по quickstart. Ветка master не переключалась,
commit/push не выполнялись в рамках запроса на новый spec-kit.


## Convergence

Read-only converge после implement сверил 8 FR, 4 SC, 10 acceptance scenarios,
все решения plan и применимые правила конституции 2.6.0. Findings 0: missing=0,
partial=0, contradicts=0, unrequested=0. Новые задачи не добавлены, converge
не переписывал tasks. После завершения assessment implement отметил T014.
14/14 tasks выполнены. Hooks отсутствуют. Требование публикации updates выполнено
одной записью 054, новый блок UI не добавлялся. Все файлы принадлежат этой фиче.
