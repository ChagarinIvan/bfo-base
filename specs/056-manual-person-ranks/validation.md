# Validation, 2026-11-08

## Spec-kit

Specify/clarify: 2 user answers, add+delete only; manual juniors authoritative regardless
of birthday/age. Date/expiry rules retained. Checklist 7/7 PASS. Two read-only research
agents examined extension points and UI, then reviewed concrete implementation.
Plan/review -> tasks -> read-only analyze -> implement completed; hooks absent.
Current branch master preserved, no commit/push/deploy.

## Regression-first

- New API tests RED with route 404 after correcting initial test auth setup (SanctumUser
  has no dedicated factory). GREEN after source/schema/API implementation: 2 tests/34 assertions.
- Pure domain: 3 tests/14 assertions, source audit/events and duplicate criteria.
- Application: repository/Person mocks, source saved before facts collection, rank state/reason/actor.
- Same-day mixed source regression RED: nullable protocol ID sorted manual before protocol;
  explicit discriminator corrected it, reversed-input order now stable.
- Prune regression RED: manual-only person incorrectly disabled by persons:prune.
  Active source NOT EXISTS exclusion protects it, while truly empty person is still disabled.
- Frontend reviewer found stale deletion callback on unmounted same-person tab; live guard
  and component regression prevent history requests/router mutation after unmount.

## Behavior evidence

| Behavior | Evidence |
| --- | --- |
| Persistent source, two rebuilds, junior without birthday | ManualPersonRankActionTest |
| Active duplicate 409, deleted tuple reusable | ManualPersonRankActionTest |
| Guest mutations and invalid date/rank input rejected | ManualPersonRankActionTest |
| Cross-person deletion 404 | ManualPersonRankActionTest |
| Two manual facts extend each other, future I/КМС activation scheduled daily | ManualPersonRankActionTest |
| Manual not a third start, mixed promotion preserves manual identity | ManualPersonRankActionTest |
| Source/projection/Person metadata rollback on failure, no source event | ManualPersonRankActionTest |
| Actual refill and protocol cleanup preserve source | ManualPersonRankActionTest |
| Real commit delivers Added/Disabled once, projection already persisted | ManualPersonRankEventsTest |
| SQLite migration up/down/up preserves protocol rows | ManualPersonRankMigrationTest |
| MySQL nullable foreign keys/source storage | API integration + real commit tests |
| UI source label, no fake event links/empty lookups, stable delete ID | PersonRanksPage.test.ts |
| Dialog required fields, API failure input preservation, duplicate submit, unmount, success | ManualPersonRankDialog.test.ts |
| New calculation reason captions | PersonPromptPersonInfo.test.ts |

Source events test uses DatabaseTruncation with explicit cleanup, allowing a genuine
commit outside test transaction. An initial DatabaseMigrations teardown hit a pre-existing
2025 ranks migration rollback defect; no historical migration was changed. New migration
roundtrip is checked separately; downgrade requires old-code refill as documented in quickstart.

## Final gates

| Gate | Result |
| --- | --- |
| composer cs | PASS, no changes needed |
| composer stan | PASS, 1000 files, no errors |
| Rector dry-run | PASS, no changes |
| npm run ci | PASS, lint/Prettier, typecheck, 86 files/302 tests, build |
| Full composer test | 741 tests executed, 5652 assertions; 740 pass, one unrelated XLSX byte-equality failure |
| Final scoped PHP after correction | PASS, 14 tests/140 assertions, includes manual API, Application and complete XLSX export test class |
| Real commit/source integration scope | PASS, 8 tests/82 assertions before final prune addition |
| git diff --check | PASS |

Full PHP completed once in final phase. The only full-run failure was an existing XLSX
alias test comparing two ZIP archives including variable timestamps. Replaced raw-byte
comparison with workbook sheet names/data equality; entire export class and new source
scenarios pass on scoped repeat. Other 740 tests passed full run, including all new source
tests, real commit, migration, startup smoke and existing query-count regressions.
No claim of a second full-suite green run is made. Initial frontend pipeline stopped at
one attribute-order warning before tests; fixed using ESLint and completed the pipeline.
Vite retains its existing chunk-size warning; PHPUnit retains 15 existing notices.

## Architecture and queries

New aggregate/input/events/ports are framework-independent. Infrastructure maps SQL records
and dispatches plain events afterCommit. Person row lock serializes add/disable/duplicate
checks; source and synchronous nested rebuild share the transaction. No source persistence
in the disposable history table, no fake protocol IDs, no physical business deletion.
New commands expose primitives/domain input, not transport DTO getters.
Collector adds one indexed manual-source query per Person, no per-history-row queries.
History provenance comes from already loaded scalar fields; API history introduces no N+1.
Prune uses indexed correlated NOT EXISTS, not per-person application queries.

## Acceptance and rollout

All FR-001–012 and SC-001–005 traced to behavior above; one updates entry 056 added.
Before running the new code against a non-test database, apply `php artisan migrate`.
Only bfo_base_test and isolated SQLite were migrated during validation. No production data
or dev database was migrated. Manual ranks follow existing expiry periods, including
historical expired facts, and bypass only the junior age filter as confirmed by user.

## Convergence

Read-only assessment of present implementation against spec/plan/tasks and constitution:
missing=0, partial=0, contradicts=0, unrequested=0. No new tasks appended; assessment
left tasks unchanged. Implement then marked T012 complete. All 12 tasks are complete.
No additional architecture exceptions or out-of-scope product behavior introduced.

## Review follow-up: formatting and property hooks

По запросу пользователя PHP-файлы фичи отформатированы с пустыми строками между
методами, namespace/imports и объявлением класса. Использован php-cs-fixer, без
массового изменения остального репозитория. AddManualPersonRank и DisableManualPersonRank
используют get-only property hooks; service callers обновлены. Transport DTO остаются
private readonly. Классы final с отдельными readonly полями: PHP запрещает hooked
properties в readonly class, как и в существующих Login/Logout command patterns.
Проверки: 9 scoped Application/API tests, 90 assertions PASS; CS PASS (1122 files),
PHPStan изменённого Application scope PASS, Rector scope dry-run PASS, diff check PASS.
