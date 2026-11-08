# Tasks: Manual person ranks

## Setup
- [x] T001 Specify/clarify: spec.md and checklists/requirements.md, 2 answers.
- [x] T002 Research/plan/review: plan.md, research.md, data-model.md, contracts/api.md, quickstart.md.

## Foundation
- [x] T003 Regression-first tests for source domain, API rebuild persistence/rollback in tests/Domain/Person/ManualRank and tests/Feature/Api/V1/PersonRankHistory/ManualPersonRankActionTest.php.
- [x] T004 Pure domain source, duplicate factory, repository port, events and Infrastructure mapper in app/Domain/Person/ManualRank and app/Infrastructure/Laravel/Eloquent/Person/ManualRank.
- [x] T005 Schema/source identity via migration, RankFact, PersonRankHistory and DTO/assembler.

## US1, P1, add and persistent calculation
Independent test: manual-only/mixed rebuild twice, junior absolute, future activation.
- [x] T006 [US1] Collector merges sources; RankCalculator preserves identity, extends null-event sources, excludes manual starts and schedules future activation; excludes active sources from automatic prune.
- [x] T007 [US1] Add command/service/DTO/action, provider/route wiring, explicit calculation reason.

## US2, P1, remove
Independent test: source inactive, projections refreshed; ownership/rollback.
- [x] T008 [US2] Disable command/service/action, transaction and scoped criteria; API regressions.

## US3, P2, provenance and SPA
Independent test: mixed/manual-only display, auth add/delete, validation and route-safe refresh.
- [x] T009 [US3] API types/wrappers, manual dialog, source-specific actions, null ID filtering in resources/spa.
- [x] T010 [US3] Component/API regressions, by.json and updates entry.

## Polish
- [x] T011 Narrow regressions, quality gates, full PHP/frontend, application/N+1 checks, validation.md.
- [x] T012 Acceptance/contract/checklist reconciliation and converge.

Dependencies: T001→T002→T003(red)→T004/T005→T006→T007→T008→T009→T010→T011→T012.
Independent domain/UI modules can be developed in parallel after contract; shared files sequential.
Strategy: persistent source foundation, add/rebuild, disable, SPA, final verification.
