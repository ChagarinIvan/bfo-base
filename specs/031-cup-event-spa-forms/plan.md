# Implementation Plan: Cup Event SPA Forms

**Branch**: `031-cup-event-spa-forms` | **Date**: 2026-09-20 | **Spec**: [spec.md](spec.md)

## Summary

Replace legacy Blade cup-stage create/edit flows with authenticated SPA routes.
A small V1 API completes the CupEvent read and mutation contract; target
Application/Domain services own mutation and route-ownership validation.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript, Vue 3
**Primary Dependencies**: Laravel 13, PrimeVue, Axios, Pagerfanta
**Storage**: MySQL 8.4 via Eloquent adapters behind Domain repositories
**Testing**: PHPUnit request/Application tests; Vitest/Vue Test Utils
**Target Platform**: Laravel monolith with Vite-served SPA
**Project Type**: Web application (V1 API plus SPA)
**Performance Goals**: Event selection remains server-paginated; no new N+1.
**Constraints**: camelCase V1 input, Sanctum mutations, Belarusian SPA only.
**Scale/Scope**: One form, V1 view/create/update actions, routes/links and
legacy form retirement.

## Constitution Check

| Gate | Status | Evidence |
|---|---|---|
| Target layers only | PASS | New commands/services use Application and existing Domain port/Infrastructure adapter. |
| Thin V1 boundary | PASS | Actions bind DTOs, create commands and return view DTOs. |
| API/SPA contract | PASS | CamelCase DTO, Bearer auth and request tests are planned. |
| Tests | PASS | Unit, request and SPA regression coverage is planned. |
| SPA language/UI | PASS | PrimeVue card/form pattern and `by.json` only. |

## Project Structure

```text
app/{Application,Domain,Infrastructure,Bridge}/...CupEvent...
resources/spa/{api,pages/cups,router}/
tests/{Application/Service/CupEvent,Feature/Api/V1/Cup}/
```

**Structure Decision**: Extend existing target Cup/CupEvent modules; no legacy
`app/Services` or `app/Repositories` code is added.

## Delivery Phases

1. Add target CupEvent DTO, factory/updater commands and services.
2. Expose authenticated V1 view/create/update actions and request tests.
3. Build the shared SPA form and create/edit pages; rewire cup-card links.
4. Remove replaced Blade controllers, routes, views and tests.
5. Run quality gates and confirm bounded event picker queries.

## Complexity Tracking

No constitution violations.
