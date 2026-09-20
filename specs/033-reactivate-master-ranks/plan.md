# Implementation Plan: Restore repeat master-rank activation

**Branch**: `033-reactivate-master-ranks` | **Date**: 2026-09-20 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/033-reactivate-master-ranks/spec.md`

**Note**: This template is filled in by the `$speckit-plan` command; its definition describes the execution workflow.

## Summary

Before any rank rebuild, legacy identification will fill `activate_rank` on a newly
identified KMS or MS protocol line when the same athlete already has another activated
line of that exact rank. The shared legacy helper will be called after both fast
database identification and queue/console identification, before their existing rebuild
dispatches. First-time and previously unactivated KMS/MS remain pending manual action.

## Technical Context

<!--
  ACTION REQUIRED: Replace the content in this section with the technical details
  for the project. The structure here is presented in advisory capacity to guide
  the iteration process.
-->

**Language/Version**: PHP 8.5

**Primary Dependencies**: Laravel 13, Eloquent, PHPUnit 13

**Storage**: MySQL 8.4 (`protocol_lines`, events and distances)

**Testing**: PHPUnit domain/feature coverage; targeted test execution during implementation

**Target Platform**: Laravel web worker and scheduled console command

**Project Type**: Monolithic web application with legacy protocol parsing/identification

**Performance Goals**: One bounded existence lookup per distinct athlete/rank during an
identification batch; no full-history loading into PHP.

**Constraints**: `activate_rank` is persisted before rebuild; only KMS and MS are in
scope; no migration or SPA/API contract change.

**Scale/Scope**: Protocol lines assigned by fast SQL identification and the queued
`protocol-lines:queue-ident` command.

## Constitution Check

*GATE: Passed before Phase 0 research and after Phase 1 design.*

- Existing parsing/identification is explicitly legacy; this narrowly fixes it rather
  than expanding the feature into its migration.
- The change has regression tests for both workflows and preserves activation before
  rebuild. Eloquent records are used only in feature/integration tests.
- No V1 API action, DTO, manual response or SPA localisation is introduced.
- The helper reuses the legacy service already responsible for identification. A future
  target-layer migration is intentionally out of scope.

## Project Structure

### Documentation (this feature)

```text
specs/033-reactivate-master-ranks/
├── plan.md              # This file ($speckit-plan command output)
├── research.md          # Phase 0 output ($speckit-plan command)
├── data-model.md        # Phase 1 output ($speckit-plan command)
├── quickstart.md        # Phase 1 output ($speckit-plan command)
├── contracts/           # Phase 1 output ($speckit-plan command)
└── tasks.md             # Phase 2 output ($speckit-tasks command - NOT created by $speckit-plan)
```

### Source Code (repository root)
<!--
  ACTION REQUIRED: Replace the placeholder tree below with the concrete layout
  for this feature. Delete unused options and expand the chosen structure with
  real paths (e.g., apps/admin, packages/something). The delivered plan must
  not include Option labels.
-->

```text
app/
├── Services/ProtocolLineIdentService.php             # legacy shared identification helper
├── Bridge/Laravel/Console/Commands/IdentProtocolLineCommand.php
├── Domain/ProtocolLine/ProtocolLine.php
└── Repositories/ProtocolLinesRepository.php           # existing fast identification SQL

tests/
├── Feature/Rank/                                      # fast database identification coverage
└── Bridge/Laravel/Console/Commands/                   # queue-ident command coverage
```

**Structure Decision**: The feature deliberately modifies the existing legacy
identification boundary. No new target-layer repository or application service is added,
because migrating parsing/identification is outside this focused bug fix.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
