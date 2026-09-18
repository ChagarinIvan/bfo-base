# Implementation Plan: Count-Free Slice Pagination

**Branch**: `027-count-free-slice-pagination` | **Date**: 2026-09-17 | **Spec**: [spec.md](spec.md)

## Summary

Replace the current Pagerfanta-backed `Slice`, whose `getNbResults()` invokes an Eloquent `count()`, with a count-free page slice. Each page query reads exactly `perPage + 1` rows, trims the extra row from the public items, and exposes `hasNext` through `X-Pagination-Has-Next`. Existing page/perPage requests, DTO payloads, authorization, filtering semantics, and deterministic ordering remain intact. All paginated listing repositories and SPA consumers are audited; only measured, query-specific indexes and join/resource changes are introduced.

## Technical Context

**Language/Version**: PHP 8.5; TypeScript for SPA

**Primary Dependencies**: Laravel 13, Eloquent, existing Pagerfanta integration to be removed or isolated from the Slice path, Axios/PrimeVue SPA components

**Storage**: MySQL 8.4

**Testing**: PHPUnit via Composer, Laravel API/integration tests, Vitest/TypeScript SPA tests, SQL query listener assertions, PHPStan and PHP CS Fixer

**Target Platform**: Laravel web application with authenticated API and Vue SPA

**Project Type**: Web application (backend API plus SPA)

**Performance Goals**: No total-count query in any Slice response; one bounded page read of `perPage + 1`; 95% of heavy listing requests under 500 ms for the first page and 750 ms for filtered pages on the agreed representative dataset

**Constraints**: Preserve page/perPage navigation, current access and DTO semantics, stable unique ordering, no new N+1, no broad unmeasured indexes, and no cursor-token migration in this feature

**Scale/Scope**: All current paginated listing paths: persons, events, groups, competitions, clubs, person payments, person prompts, protocol lines, rank checks and rank-check rows; non-paginated `all()`/reference and batch/console reads are out of scope unless the audit finds they share the Slice response path

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Layering**: PASS. Pagination behavior belongs in Domain Shared; Eloquent query adapters and indexes remain Infrastructure; API header mapping remains Bridge; SPA changes remain frontend presentation.
- **Dependency inversion**: PASS. Application services keep repository ports and Criteria; the Domain Slice contract must not require Eloquent or Laravel facades.
- **Testing**: PASS. The foundational no-`COUNT` test is isolated from listing-specific integration coverage; Application/Domain unit tests use collaborators/mocks, while database records are used only in integration/API tests.
- **Target architecture**: PASS. The feature updates existing target-layer pagination/query code and does not expand legacy `app/Services` or `app/Repositories`.
- **Performance/N+1**: PASS. Query profiles, SQL-count assertions and relation-loading checks are part of the design and completion gates.
- **Migration safety**: PASS. Index changes are additive and reversible where practical, with schema and data-preservation checks.

## Project Structure

```text
app/
├── Domain/Shared/Pagination/Slice.php
├── Infrastructure/Laravel/Eloquent/Pagination/EloquentQueryAdapter.php
├── Infrastructure/Laravel/Eloquent/{Person,Event,Group,Competition,Club,PersonPayment,PersonPrompt,ProtocolLine,RankCheck}/
└── Bridge/Laravel/Http/Controllers/ApiAction.php
resources/spa/
├── api/types.ts
├── pages/listingModels.ts
├── components/ListingTable.vue
└── pages/**/                 # paginated listing consumers
database/migrations/          # query-specific indexes
tests/
├── Domain/Shared/Pagination/
├── Infrastructure/Laravel/Eloquent/
├── Feature/Api/V1/
└── resources/spa-equivalent tests near SPA modules
```

**Structure Decision**: Keep the existing backend/domain/infrastructure/bridge and SPA layout. Introduce no new top-level project or framework. The shared Slice and adapter are the single pagination seam; repositories continue to build their own criteria queries behind existing ports.

## Phase 0: Research Outputs

- [research.md](research.md) resolves the Pagerfanta/count behavior, the page-based Slice algorithm, API header compatibility, listing inventory, and index/query-profile approach.

## Phase 1: Design Outputs

- [data-model.md](data-model.md) defines Slice state, listing criteria/resources, and query profiles.
- [contracts/api.md](contracts/api.md) defines response headers and compatibility rules.
- [contracts/ui.md](contracts/ui.md) defines the SPA navigation model without total/last page.
- [quickstart.md](quickstart.md) defines runnable validation scenarios and quality gates.

## Post-Design Constitution Check

- **PASS**: The design keeps framework-specific query execution in Infrastructure and transport header mapping in Bridge.
- **PASS**: The one mandatory no-`COUNT` regression test validates the shared Slice seam; listing tests validate filtering, joins, relations, ordering and pagination behavior.
- **PASS**: Removing `Total`/`Last-Page` is an intentional API contract change recorded in the spec and contracts; no fake values or compatibility count query are permitted.
- **PASS**: Index decisions are evidence-based and documented per query profile, avoiding speculative schema growth.

## Complexity Tracking

No constitution violations identified; no complexity exception is required.
