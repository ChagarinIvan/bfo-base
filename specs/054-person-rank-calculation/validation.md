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
| Protocol rank stage | event_results_processed | StandardEventPersonRankUpdaterTest |
| Cleanup/replacement | event_results_cleared | ReplaceEventProtocolTest, affected и removed Person |
| Reassignment, new/old Person | protocol_person_changed | AssignPersonOnProtocolLinesServiceTest, typed mutation argument; shared service passes same reason for both branches |
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

## Уточнение причин 2026-11-06

Red: assignment regression падает до добавления ProtocolPersonChanged.
Green: 22 tests / 151 assertions, включая три сценария и все семь enum cases
в сохранении и очереди. Старый protocol проверяется тем же data provider
и существующим API тестом; миграция и backfill не нужны. Новых запросов нет.

Quality: composer cs, composer stan, Rector dry-run PASS. Frontend lint/Prettier
и typecheck PASS. Общий Vitest: 281 PASS, два таймаута в protocolLineImpressions;
изолированный повтор файла: 24/24 PASS. Vite build PASS.

Полный PHP: 723 tests / 5510 assertions PASS, 15 прежних PHPUnit notices.
Включены HTTP startup smoke и существующие N+1 regression tests.
Сверка spec/contract/checklist: три production callers используют разные причины,
старое protocol читается без переписывания истории. T015–T017 выполнены.

## Обязательная причина 2026-11-06

Исходная миграция добавляет NOT NULL reason с временным default unknown, затем
удаляет default. Person инициализирует Unknown в приложении. Время nullable.
Red: migration regression ожидал unknown и получил null.
Green: 16 integration/API/migration tests, 89 assertions. Проверены backfill,
NOT NULL, отсутствие DB default, отклонение null/пропущенного значения, up/down/up.
Добавлена отдельная проверка MySQL schema и нового Person.
Два старых unit-мока чтения Person обновлены под обязательный enum,
Person Application unit suite: 16 tests PASS.
Frontend CI PASS: lint/Prettier, typecheck, 85 files / 283 tests, Vite build.

Полный прогон: 725 tests, 5512 assertions, 15 прежних notices.
Обнаружены только две ошибки старых mocks ListPersons/ViewPerson, описанные выше.
После исправления повторён весь Person Application unit suite: 16 tests /
73 assertions PASS; остальные 723 tests полного прогона PASS, включая MySQL
schema, HTTP startup и N+1. Полный набор повторно не запускался.
Финальные CS, STAN и Rector dry-run PASS. Новых запросов в production нет.
Spec/contract/checklist сверены, T018–T019 выполнены. Изменена исходная миграция;
на уже мигрированных БД эта правка автоматически не выполняется.

## Карточка Person, 2026-11-06

Regression-first: до реализации 9 новых auth/date/reason tests FAIL, guest PASS.
После реализации PersonPromptPersonInfo: 15/15 PASS. Проверены все восемь reasons,
положение сразу под текущим разрядом, время с секундами, null timestamp,
unknown reason и отсутствие строк у guest даже при populated API response.
Используются существующие данные getPerson и формат даты impressionModels,
новых запросов/relations нет. PHP-код и API-контракт не менялись.

Frontend CI PASS: ESLint/Prettier, vue-tsc, 85 files / 293 tests, Vite build.
git diff --check PASS. Backend suites не повторялись: PHP и зависимости неизменны.
Spec/contract/checklist сверены, T020–T021 выполнены, одна существующая updates
запись 054 обновлена. Commit/push не выполнялись.
