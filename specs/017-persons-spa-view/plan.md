# Implementation Plan: SPA View Person

**Branch**: 017-persons-spa-view | **Date**: 2026-09-06 | **Spec**: [spec.md](spec.md)

## Summary

Migrate the public Blade View Person entry point to /app/persons/:personId. Reuse the existing PersonPromptPersonInfo component as a persistent parent layout, preserve the five person destinations, and add a paginated protocol-line API/query for the participation table. The query will support person ownership, year, competition name and event date filters, while typed ProtocolLineResources make event and competition loading explicit. Standard protocol-line reads use EloquentProtocolLinesRepository; cup/identification-specific operations remain in the legacy adapter until a later refactoring.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript, Vue 3

**Primary Dependencies**: Laravel 13, Eloquent, Pagerfanta, Axios, PrimeVue, Vue Router, Vitest

**Storage**: Existing MySQL tables protocol_lines, distances, events, competitions, person

**Testing**: PHPUnit 13 application/API tests; Vitest SPA/API tests; PHPStan, PHP-CS-Fixer, Rector, frontend CI

**Target Platform**: Laravel API and browser SPA served by the existing application shell

**Project Type**: Full-stack web application

**Performance Goals**: One count query and one page query for the protocol-line list, plus bounded eager-loading queries for requested event/competition resources; no query per returned row

**Constraints**: Preserve current public read/auth behavior while moving person-detail links to the SPA; do not add new legacy services or repositories

**Scale/Scope**: One person context and one paginated page of protocol lines per request; existing shared pagination maximums apply

## Constitution Check

- **Layering**: API actions stay in Bridge, orchestration in Application, resource/query contracts in Domain, and Eloquent query changes in the existing protocol-line repository adapter.
- **No legacy expansion**: No new code is added to app/Services. Standard protocol-line reads use the Infrastructure adapter; the existing legacy repository is retained only for operations not yet represented by the domain port.
- **Commands and resources**: ListProtocolLines carries the search DTO and exposes Criteria plus typed ProtocolLineResources; the action calls one Application service.
- **Testing**: Unit tests mock repositories/collaborators; API tests create real records; SPA tests cover route, filters, states and stale responses.
- **N+1**: The repository eagerly loads distance.event, distance.group and event.competition according to resources; a query-count regression test is required.
- **Status**: PASS. No constitutional exception is required.

## Architecture and Design

### Backend

1. Add Domain ProtocolLineResources and extend ProtocolLineRepository with paginated reads accepting resources while preserving existing default behavior for current callers.
2. Add SearchProtocolLineDto, ListProtocolLines command, ViewProtocolLineDto and ProtocolLineAssembler in Application.
3. Add ListProtocolLinesService. It passes Criteria/resources to ProtocolLineRepository::paginate and maps the slice; the repository applies the active-person filter when personId is supplied, so an unknown or inactive person produces an empty result.
4. Add ListProtocolLinesAction and register GET /api/v1/protocol-lines before any potentially conflicting parameterized routes under optional API auth.
5. Implement EloquentProtocolLinesRepository with person/year/competitionName/date filters, active-person filtering, stable event-date/id ordering, pagination and typed relation loading. Keep cup and identification-specific methods in app/Repositories/ProtocolLinesRepository.php until a later refactoring.

### Frontend

1. Add protocolLines API helper and TypeScript types.
2. Add PersonLayoutPage with PersonPromptPersonInfo and a shared five-destination navigation block; render the selected person section through a nested RouterView.
3. Add PersonViewPage as the participation child page, and nest payments/prompts (including their forms) under /app/persons/:personId so the Person Info card is not remounted during tab navigation.
4. Reuse shared loading/error/empty, pagination, debounce and stale-request patterns from CompetitionsPage and GroupDetailsPage.
5. Add translations for page title, actions, filters, table columns and states.

### Compatibility

- Remove the Blade View Person and `/persons/{person}/show` route after migrating all known links and redirects to `/app/persons/{person}`.
- Keep legacy person create/edit forms, rank history and extract action because they remain outside this SPA migration or are still used by authenticated workflows.
- Keep LegacyViewPersonDto and its protocol-line mapping for existing consumers.
- New API DTOs are separate from legacy grouped-by-year DTOs to avoid transport coupling.

## Project Structure

~~~
app/
├── Application/Dto/ProtocolLine/
│   ├── SearchProtocolLineDto.php
│   ├── ViewProtocolLineDto.php
│   └── ProtocolLineAssembler.php
├── Application/Service/ProtocolLine/
│   ├── ListProtocolLines.php
│   └── ListProtocolLinesService.php
├── Bridge/Laravel/Http/Controllers/Api/V1/ProtocolLine/
│   └── ListProtocolLinesAction.php
├── Domain/ProtocolLine/
│   ├── ProtocolLineResources.php
│   ├── ProtocolLineOperations.php
│   └── ProtocolLineRepository.php
└── Infrastructure/Laravel/Eloquent/ProtocolLine/
    └── EloquentProtocolLinesRepository.php

app/Repositories/
└── ProtocolLinesRepository.php  # temporary adapter for non-standard legacy operations

resources/spa/
├── api/
│   ├── protocolLines.ts
│   └── types.ts
├── pages/persons/
│   ├── PersonLayoutPage.vue
│   ├── PersonViewPage.vue
│   └── PersonViewPage.test.ts
├── components/
│   └── PersonInfoNavigation.vue
└── router/index.ts

tests/
├── Application/Service/ProtocolLine/ListProtocolLinesServiceTest.php
└── Feature/Api/V1/ProtocolLine/ListProtocolLinesActionTest.php
~~~

**Structure Decision**: Follow the existing Laravel layered application and resources/spa Vue structure. EloquentProtocolLinesRepository is the adapter for the ProtocolLineRepository port. ProtocolLinesRepository remains a narrow temporary adapter for cup/identification operations that are not part of the port, while ProtocolLineOperations keeps final service consumers mockable without making the service itself non-final.

## Delivery Phases

1. Backend contract and query: tests, DTO/command/resources, service, repository pagination and API action.
2. SPA page: API helper/types, route, page, filters/table/actions/translations and focused Vitest coverage.
3. Integration and cleanup: API route audit, N+1 assertion, format/static checks and full backend/frontend suites.

## Complexity Tracking

No constitution violations or additional complexity exceptions.
