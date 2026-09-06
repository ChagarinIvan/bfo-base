# Implementation Plan: SPA-оплаты персоны

**Branch**: '016-persons-spa-payments' | **Date**: 2026-09-05 | **Spec**: [spec.md](spec.md)

## Summary

Перенести просмотр и добавление/обновление оплат персоны из Blade в SPA. Backend
сохраняет существующий PersonPayment domain/application flow, добавляя API
read endpoint и authenticated mutation endpoint на command-based Application
services. Frontend получает list/form routes по паттернам feature 015. После
аудита удаляются только payment-only web routes/actions/views; repository,
factory, protocol/rank consumers и shared payment queries сохраняются.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript/Vue 3

**Primary Dependencies**: Laravel 13, Eloquent, Axios, Vue Router, PrimeVue, Vitest

**Storage**: MySQL 8.4, существующая таблица persons_payments

**Testing**: PHPUnit/Laravel feature tests, Application unit tests, Vitest, npm run ci

**Target Platform**: Laravel web application with Vue SPA mounted under /app

**Project Type**: Web application (Bridge/API + SPA)

**Performance Goals**: One bounded payment-list count query plus one page query;
no per-row relation queries or unbounded relation loading

**Constraints**: Preserve existing create-or-update-by-person-and-year behavior,
auth-only payment access for read and mutation, no new legacy
services/repositories, no Laravel-specific code in Domain service contracts

**Scale/Scope**: One person payment list and one date form; no delete flow,
standalone edit flow or schema migration in this feature

## Constitution Check

*GATE: Must pass before Phase 0 research and after Phase 1 design.*

- **Layering**: PASS. New orchestration is in Application services; HTTP adapters
  are Bridge and Eloquent/API details stay outside Domain contracts.
- **Commands and criteria**: PASS. API actions create commands; list queries use
  Criteria; mutation reuses the existing factory, lock and transaction policy.
- **Repository boundaries**: PASS. Extend/reuse existing payment repository and
  person query ports; do not add a legacy repository or special finder.
- **Testing**: PASS. API/request and SPA tests cover changed behavior; Application
  tests use mocks and do not create Eloquent entities.
- **No facade/legacy expansion**: PASS. No new code is added to app/Services;
  no Laravel facade is introduced.
- **Performance**: PASS. Payment rows are selected by one repository query and
  shared consumers are audited for N+1 regressions.

## Research Summary

See research.md. The main decisions are:

1. Use `/api/v1/persons/payments` for authenticated list and mutation. The list
   receives `personId`, optional `year`, `page` and `perPage` in query; mutation
   receives required `personId` in body.
2. Reuse the shared `Slice` pagination contract and common SPA year-filter component.
3. Hide active-person filtering in the payment repository, matching prompts;
   unavailable persons produce an empty paginated list.
4. Reuse ViewPerson/person info data for the SPA header only where needed; the
   payment list itself returns payment DTOs and does not eager-load relations.

## Project Structure

### Documentation

    specs/016-persons-spa-payments/
    ├── spec.md
    ├── plan.md
    ├── research.md
    ├── data-model.md
    ├── quickstart.md
    ├── contracts/api.md
    ├── checklists/requirements.md
    └── tasks.md

### Source Code

    app/Application/Service/PersonPayment/
    ├── ListPersonsPayments.php
    ├── ListPersonsPaymentsService.php
    ├── CreateOrUpdatePersonPayments.php
    └── CreateOrUpdatePersonPaymentsService.php

    app/Bridge/Laravel/Http/Controllers/Api/V1/PersonPayment/
    ├── ListPersonPaymentsAction.php
    └── CreateOrUpdatePersonPaymentAction.php

    app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php
    app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php
    resources/views/persons/show.blade.php
    resources/spa/api/personPayments.ts
    resources/spa/api/types.ts
    resources/spa/pages/persons/PersonPaymentsPage.vue
    resources/spa/pages/persons/CreatePersonPaymentPage.vue
    resources/spa/pages/persons/PersonPaymentForm.vue
    resources/spa/router/index.ts
    tests/Feature/Api/V1/PersonPayment/
    tests/Application/Service/PersonPayment/
    resources/spa/pages/persons/*.test.ts

**Structure Decision**: Keep the existing Laravel monolith and Vue SPA structure.
Application services remain the use-case boundary, Bridge actions own HTTP
translation, and existing Domain/Infrastructure payment classes remain the
persistence/domain boundary.

## Implementation Phases

### Phase 0 — Research

- Confirm current payment routes, validation, create-or-update semantics and all
  shared consumers.
- Confirm public/auth API middleware patterns and SPA API response conventions.
- Confirm legacy cleanup can remove only payment-only entry points.

### Phase 1 — Design

- Define payment DTOs and endpoint behavior in contracts/api.md.
- Define person/payment/form entities and lifecycle in data-model.md.
- Define runnable backend/frontend validation in quickstart.md.
- Re-evaluate constitution gate after design artifacts.

### Phase 2 — Implementation

- Add request/API tests before implementation for list, not-found, auth,
  validation, create and same-year update.
- Add SPA API types, routes, list states, form and Vitest coverage.
- Update person detail link and remove audited payment-only Blade artifacts.
- Run shared payment consumer regression tests and final quality gates.

## Complexity Tracking

No constitution violations or additional projects are required.
