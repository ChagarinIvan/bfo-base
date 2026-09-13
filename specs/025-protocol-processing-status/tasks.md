---

description: "Задачи: статусы обработки протокола этапа"
---

# Tasks: Статусы обработки протокола этапа

**Input**: Артефакты из `specs/025-protocol-processing-status/`

**Prerequisites**: [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), [contracts](contracts/), [quickstart.md](quickstart.md)

**Tests**: PHPUnit unit/API и Vitest обязательны по конституции и спецификации.

## Phase 1: Setup

- [X] T001 Add localized protocol-processing labels, warnings and errors in `resources/spa/i18n.ts`.
- [X] T002 Add processing-status transport types in `resources/spa/api/types.ts`.

## Phase 2: Foundational

- [X] T003 Add `EventProtocol` aggregate, `events.active_event_protocol_id`, `protocol_lines.event_protocol_id`, run token, rank-batch identity, indexes, casts, intentional transition methods and domain events in `app/Domain/Event/`, `app/Domain/ProtocolLine/`, `database/migrations/`, and `app/Domain/Event/Event.php`.
- [X] T004 Add state-transition/domain-event unit coverage, including duplicate line-identification and duplicate/stale rank-batch completion, in `tests/Domain/Event/`.
- [ ] T004a Add an idempotent historical-data migration and integration coverage: persisted historical protocol results receive a ready active run; events without protocol and ambiguous/partial data do not become public in `database/migrations/` and `tests/Feature/`.

## Phase 3: User Story 1 — Видеть обработку нового протокола (P1)

**Goal**: после создания/изменения этапа пользователь видит реальный ход обработки.

- [ ] T005 [P] [US1] Add EventProtocol parser, ProtocolLine identification event, idempotent event-scoped rank-batch completion, duplicate-delivery and stale-run tests in `tests/Application/Handler/Event/`, `tests/Domain/ProtocolLine/` and `tests/Feature/Console/`.
- [X] T006 [P] [US1] Add authenticated event-status API coverage in `tests/Feature/Api/V1/Event/`.
- [X] T007 [US1] Attach parsed lines to `EventProtocol`; orchestrate `ProtocolLine` aggregate identification events, atomic idempotent line accounting, one stored event-scoped rank batch and matching completion signals before EventProtocol transitions in `app/Application/Service/Event/`, `app/Application/Handler/Event/`, rank-rebuild jobs and `app/Bridge/Laravel/Console/`; console command remains Bridge-only and no status is assigned outside aggregate methods.
- [X] T008 [US1] Expose authenticated processing status through event DTOs, assemblers, services and V1 actions in `app/Application/Dto/Event/`, `app/Application/Service/Event/`, and `app/Bridge/Laravel/Http/Controllers/Api/V1/Event/`.
- [X] T009 [P] [US1] Add status UI component/model tests in `resources/spa/components/EventProcessingStatus.test.ts` and `resources/spa/pages/events/eventProcessingModels.test.ts`.
- [X] T010 [US1] Render localized status, warning and loader in `resources/spa/components/EventProcessingStatus.vue` and `resources/spa/pages/events/EventViewPage.vue`.
- [X] T011 [US1] Add five-second transition-only polling with cancellation and retry notice coverage in `resources/spa/pages/events/EventViewPage.test.ts` and implementation in `resources/spa/pages/events/EventViewPage.vue`.

## Phase 4: User Story 2 — Понимать итог и сбой обработки (P1)

- [X] T012 [P] [US2] Add public ready-only list/detail request tests in `tests/Feature/Api/V1/Event/`.
- [X] T013 [US2] Restrict public event queries to ready protocol states while retaining authenticated access in `app/Infrastructure/Laravel/Eloquent/Event/` and `app/Application/Service/Event/`.
- [X] T014 [US2] Show terminal ready/failed UI and stop polling in `resources/spa/components/EventProcessingStatus.vue` and `resources/spa/pages/events/EventViewPage.vue`.

## Phase 5: User Story 3 — Контролировать обработку в списке этапов (P2)

- [ ] T015 [P] [US3] Add authenticated event-list status tests in `tests/Feature/Api/V1/Event/ListEventsActionTest.php` and `resources/spa/pages/competitions/CompetitionDetailsPage.test.ts`.
- [X] T016 [US3] Render processing state in the event listing column in `resources/spa/pages/competitions/CompetitionDetailsPage.vue`.

## Phase 6: User Story 4 — Не тратить запросы после ухода со страницы (P3)

- [X] T017 [US4] Verify polling timer cleanup and temporary-refresh retry behavior in `resources/spa/pages/events/EventViewPage.test.ts`.

## Phase 7: Polish

- [X] T018 Run focused PHPUnit/Vitest suites and validate `specs/025-protocol-processing-status/quickstart.md`.
- [X] T019 Run `composer cs`, `composer stan`, `composer rector -- --dry-run`, `composer test`, `npm run ci`, and inspect event queries for N+1 regressions.

## Dependencies & Execution Order

`Setup → Foundational → US1 → US2 → US3 → US4 → Polish`.

US1 establishes the persistent state and detail polling. US2 depends on the state projection; US3 reuses it in the listing; US4 completes polling lifecycle coverage.

## Parallel Opportunities

- T005/T006 and T009 can be authored in parallel before their implementations.
- T012 and T015 can proceed after the shared DTO contract is fixed.

## Implementation Strategy

Deliver US1 first, then validate a newly created protocol through every transition. Add public visibility rules in US2 before showing status in the list, and finish with lifecycle/quality gates.
