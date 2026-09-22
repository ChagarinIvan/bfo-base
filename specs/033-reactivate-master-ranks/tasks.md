# Tasks: Restore repeat master-rank activation

**Input**: Design documents from `/specs/033-reactivate-master-ranks/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: Regression coverage is required by the specification and Constitution.

## Phase 1: Setup

**Purpose**: Establish the exact legacy paths and focused regression command.

- [X] T001 Record fast-ident and queue-ident test fixtures in `tests/Feature/Rank/RepeatMasterRankActivationTest.php` and `tests/Bridge/Laravel/Console/Commands/IdentProtocolLineCommandTest.php`

---

## Phase 2: Foundational

**Purpose**: Define the shared legacy activation operation used before every rebuild.

- [X] T002 Add focused regression coverage for repeated KMS/MS activation in `tests/Feature/Rank/RepeatMasterRankActivationTest.php`
- [X] T003 Implement the bounded prior-activated-same-rank check and idempotent event-date activation in `app/Services/ProtocolLineIdentService.php`

**Checkpoint**: The common operation preserves existing dates, excludes first/unactivated prior achievements, and does not alter non-KMS/MS ranks.

---

## Phase 3: User Story 1 - Automatically confirm a repeated KMS or MS (Priority: P1) 🎯 MVP

**Goal**: Fast database identification persists activation before it schedules rebuilding.

**Independent Test**: Identify new KMS and MS lines against historical activated same-rank lines and verify event-date activation; verify negative cases remain null.

- [X] T004 [US1] Add feature regression scenarios for repeated and unactivated-prior rank cases in `tests/Feature/Rank/RepeatMasterRankActivationTest.php`
- [X] T005 [US1] Invoke the shared activation operation after fast identification and before `RebuildPersonRanksJob` dispatch in `app/Services/ProtocolLineIdentService.php`
- [X] T006 [US1] Run the focused fast-ident regression suite in `tests/Feature/Rank/RepeatMasterRankActivationTest.php`

**Checkpoint**: Fast identification produces an activation date before the rebuild boundary.

---

## Phase 4: User Story 2 - Apply the rule in every identification workflow (Priority: P1)

**Goal**: Queue/console identification follows the same activation contract.

**Independent Test**: Execute `protocol-lines:queue-ident` for an athlete with prior activated KMS/MS and verify the assigned line is activated before rank rebuild.

- [X] T007 [US2] Add console regression coverage for repeated MS assignment and activation-before-rebuild ordering in `tests/Bridge/Laravel/Console/Commands/IdentProtocolLineCommandTest.php`
- [X] T008 [US2] Invoke the shared activation operation after queue-line person assignment and before `rebuildRanks()` in `app/Bridge/Laravel/Console/Commands/IdentProtocolLineCommand.php`
- [X] T009 [US2] Run the focused console regression suite in `tests/Bridge/Laravel/Console/Commands/IdentProtocolLineCommandTest.php`

**Checkpoint**: Fast and queue identification have identical activation outcomes.

---

## Phase 5: User Story 3 - Preserve rank calculation semantics (Priority: P2)

**Goal**: Rank rebuild consumes the persisted activation and creates the expected period.

**Independent Test**: Rebuild after automatic repeat activation and verify the current/history rank starts on the event date.

- [X] T010 [US3] Add an integration regression proving queue rebuild consumes the automatically saved activation in `tests/Bridge/Laravel/Console/Commands/IdentProtocolLineCommandTest.php`
- [X] T011 [US3] Verify `app/Application/Service/Person/RebuildPersonRanksService.php` consumes activation through rank facts without mutating it
- [X] T012 [US3] Run rank-calculation regression coverage in `tests/Bridge/Laravel/Console/Commands/IdentProtocolLineCommandTest.php` and `tests/Domain/Rank/RankCalculatorTest.php`

**Checkpoint**: The resulting rank period follows the saved activation date.

---

## Phase 6: Polish and verification

**Purpose**: Validate the complete legacy flow and prevent accidental scope expansion.

- [X] T013 Update validation steps and resolved design notes in `specs/033-reactivate-master-ranks/{quickstart.md,research.md}`
- [ ] T014 Run `composer cs`, `composer stan`, `composer rector`, `composer test`, and `git diff --check` from the repository root

## Phase 7: Historical correction

- [ ] T015 Add preview and apply integration coverage for historical repeat KMS/MS correction in `tests/Bridge/Laravel/Console/Commands/BackfillRepeatMasterRankActivationCommandTest.php`
- [ ] T016 Extend shared legacy repeat-activation logic with non-mutating preview support in `app/Services/ProtocolLineIdentService.php`
- [ ] T017 Register a batched `--dry-run`/apply console command that rebuilds affected athletes only after persisting activation in `app/Bridge/Laravel/Console/Commands/BackfillRepeatMasterRankActivationCommand.php` and `app/Bridge/Laravel/Console/Kernel.php`
- [ ] T018 Run focused historical-correction coverage and PHP quality gates from the repository root

## Dependencies & Execution Order

- T001 → T002 → T003 establishes the shared behaviour.
- US1 (T004–T006) depends on T003.
- US2 (T007–T009) depends on T003 and can proceed in parallel with US1 after the helper is stable.
- US3 (T010–T012) depends on US1 because it verifies its persisted result.
- Polish (T013–T014) depends on all earlier tasks.

## Parallel Opportunities

- T004 and T007 can be written in parallel because they cover distinct workflow files.
- T006 and T009 can run in parallel after their implementations are complete.
- T010 can be prepared independently, but executes after fast-ident behaviour is available.

## Implementation Strategy

1. Add the shared legacy helper with narrow negative-case coverage.
2. Deliver and verify fast database identification first.
3. Add the console invocation and its independent regression test.
4. Verify rebuild consumes, rather than derives, activation.
5. Run full PHP quality gates once at feature completion.

## Phase 8: Production N+1 remediation

- [X] T019 Replace the per-athlete prior-activation `exists()` loop with one set-based candidate query in `app/Services/ProtocolLineIdentService.php` per plan: performance goal.
- [ ] T020 Run the multi-athlete query-count regression in `tests/Feature/Rank/RepeatMasterRankActivationTest.php` against isolated MySQL per plan: performance goal.
