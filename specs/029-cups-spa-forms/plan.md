# Implementation Plan: SPA Cup Forms and Legacy Route Removal

**Branch**: `029-cups-spa-forms` | **Date**: 2026-09-18 | **Spec**: [spec.md](spec.md)

## Summary

Expose the existing cup application services through authenticated V1 API actions, build SPA create
and edit pages using the established competition/person form patterns, register protected `/app/cups`
form routes, and remove only the legacy cup create/edit Blade actions, views, and POST routes. Existing
cup details, tables, events, exports, and delete flows remain legacy-compatible.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript/Vue 3

**Primary Dependencies**: Laravel 13, Sanctum, Vue Router, PrimeVue, Axios, Vitest

**Storage**: Existing MySQL Eloquent Cup aggregate and repository

**Testing**: PHPUnit/Feature API request tests, Vitest SPA component/router tests, PHPStan, CS, frontend CI

**Target Platform**: Laravel web application with the existing `/app` SPA shell

**Project Type**: Web application with Laravel API and Vue SPA

**Performance Goals**: Form load and save use one request per required API operation; no new unbounded queries

**Constraints**: Preserve existing CupDto validation and Application services; no Laravel facade in new code;
  use Belarusian SPA translations only; preserve unrelated legacy cup routes

**Scale/Scope**: Two authenticated admin forms, three API operations, two SPA routes, and removal of four
  legacy form routes/actions/views

## Constitution Check

- **Layering**: PASS. HTTP actions stay in Bridge, use cases remain in Application, persistence remains
  behind the Domain repository and Infrastructure implementation.
- **API command boundary**: PASS. API actions construct `AddCup`/`UpdateCup` commands and services receive
  commands rather than scalar transport data.
- **Testing**: PASS. Request tests cover authentication, validation, payload, persistence, and legacy route
  removal; Vitest covers form state, submit, errors, and routes.
- **V1 contract**: PASS. API fields use camelCase, DTO serialization remains shared, and authenticated user
  context is provided through existing middleware/ApiAction infrastructure.
- **SPA localization**: PASS. New text is added only to `resources/lang/by.json`.
- **Legacy boundary**: PASS. Only create/edit Blade routes and templates are removed; detail/table/event/
  export/delete routes remain.

## Project Structure

### Documentation

```text
specs/029-cups-spa-forms/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/
│   └── cups-api.md
├── quickstart.md
└── tasks.md
```

### Source Code

```text
app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/
├── CreateCupAction.php
├── ViewCupAction.php
└── UpdateCupAction.php
app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php
app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php
app/Application/Dto/Cup/
├── CupDto.php
└── ViewCupDto.php
app/Application/Service/Cup/
├── AddCup.php
├── AddCupService.php
├── UpdateCup.php
└── UpdateCupService.php
resources/spa/
├── api/cups.ts
├── api/types.ts
├── pages/cups/
│   ├── CupForm.vue
│   ├── CreateCupPage.vue
│   ├── EditCupPage.vue
│   └── *.test.ts
└── router/index.ts
resources/views/cup/
├── create.blade.php       # removed
└── edit.blade.php         # removed
tests/Feature/Api/V1/Cup/
└── CupFormActionsTest.php
tests/Bridge/Laravel/Http/Controllers/Cup/
└── legacy route regression coverage
```

**Structure Decision**: Follow the existing competition and person SPA form architecture. Keep shared
cup field rendering in one `CupForm.vue`; keep API transport in `api/cups.ts`; keep authenticated route
guards in Vue Router and server authorization in the API middleware.

## Implementation Phases

### Phase 0: Research

- Confirm existing CupDto rules, AddCup/UpdateCup services, DTO serializer groups, and route middleware.
- Confirm legacy route dependency graph so only create/edit paths are removed.
- Confirm SPA form/error/notification conventions from competition and person forms.

### Phase 1: Design

- Define API request/response contract for create, view, and update.
- Define Cup form state and validation/error mapping.
- Define route and legacy removal acceptance checks.

### Phase 2: Implementation

- Add request tests before API actions and SPA form implementation.
- Add API actions/routes and reuse existing Application services.
- Add SPA types, API functions, shared form, create/edit pages, routes, and translations.
- Remove legacy create/edit controllers, views, imports, and routes.

### Phase 3: Verification

- Run narrow PHP and Vitest tests during implementation.
- Finish with CS, PHPStan, frontend CI/build, route checks, and diff verification.

## Complexity Tracking

No constitution violations are planned.
