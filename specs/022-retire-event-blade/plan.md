# Implementation Plan: Retire Event Blade

**Branch**: `022-retire-event-blade` | **Date**: 2026-09-12 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/022-retire-event-blade/spec.md`

**Note**: This template is filled in by the `$speckit-plan` command; its definition describes the execution workflow.

## Summary

Migrate the remaining event-management workflows to authenticated SPA routes and JSON endpoints, then remove the event Blade route group and presentation-only legacy code. Preserve the existing Event domain operations and protocol parsing semantics; move the unite-events logic out of its controller into an Application command/service and split its create/persist/relations phases. Keep HTTP DTOs at the Bridge/Application boundary, convert protocol sources to domain values in commands, and expose domain protocol failures as Application HTTP errors.

## Technical Context

<!--
  ACTION REQUIRED: Replace the content in this section with the technical details
  for the project. The structure here is presented in advisory capacity to guide
  the iteration process.
-->

**Language/Version**: PHP 8.5, TypeScript, Vue 3, Laravel 13

**Primary Dependencies**: Laravel, PrimeVue, Axios, PHPUnit, Vitest

**Storage**: MySQL/Eloquent and existing protocol storage; no schema migration

**Testing**: PHPUnit API/integration and Application unit tests; Vitest API, router, and SPA component tests

**Target Platform**: Laravel monolith with `/app` SPA

**Project Type**: Web application

**Performance Goals**: Event forms and selectors are immediately usable after their detail request; no added N+1 queries in event or Cup rendering.

**Constraints**: All mutations require existing API authentication; protocol upload stays multipart; route identifiers stay route parameters rather than DTO fields; no Laravel facades or new `app/Services` legacy classes; Cup pages remain Blade; public event read APIs remain unchanged.

**Scale/Scope**: Four retired event Blade templates, eight legacy web actions/routes, four authenticated mutation endpoints, three SPA page flows, domain protocol/error boundaries, transactional event uniting, and cleanup of unreferenced legacy DTO/service/tests.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Bridge actions deserialize HTTP input and call Application commands/services only; route identifiers are passed explicitly; the unite-events controller logic moves to an Application service.
- Domain factories create domain values/entities; persistence ordering is orchestrated by the Application service, with relation generation after the new event is persisted.
- Use existing Domain ports and Eloquent implementations; no repository is added solely for this feature.
- Application/Domain tests mock collaborators; request tests use database fixtures.
- SPA uses existing PrimeVue and project components, does not introduce a parallel visual system.
- `LegacyViewEventDto` is removed only after Cup assemblers and views use `ViewEventDto` without exposing Eloquent distance objects.

**Result**: PASS before research and after design.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
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
app/Application/{Dto/Event,Service/Event}/
app/Bridge/Laravel/Http/Controllers/Api/V1/Event/
app/Bridge/Laravel/Provider/{ApiV1RoutesServiceProvider,WebRoutesServiceProvider}.php
app/Domain/Event/{Factory,}/
resources/spa/{api,pages/events,router}/
resources/spa/pages/competitions/CompetitionDetailsPage.vue
resources/views/cup/events/show.blade.php
tests/{Application/Service/Event,Feature/Api/V1/Event}/
tests/Bridge/Laravel/Http/Controllers/Event/
```

**Structure Decision**: Extend the existing Laravel monolith and its SPA in place. HTTP mutations use API V1 controllers; orchestration stays in Application services; the legacy Web Event controller folder is removed.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
