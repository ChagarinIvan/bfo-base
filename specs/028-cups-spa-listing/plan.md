# Implementation Plan: SPA-листинг кубков

## Constitution Check

- **Layering**: new query DTO/command/service live in `Application`, repository port and
  criteria in `Domain`, Eloquent query in `Infrastructure`, API action and routes in `Bridge`.
- **No legacy expansion**: no new code goes to `app/Services` or `app/Repositories`; the old
  listing-only action/service/search DTO are removed after callers are migrated.
- **API pattern**: thin action creates a command, service returns a `Slice`, `ApiAction` owns
  serialization and pagination headers.
- **Testing**: Application/Domain tests mock repositories; API request tests use real DB records;
  SPA tests cover filters, auth visibility and states.
- **Code style**: PHP 8.5 imports, braces on all `if` statements, no inline FQCN or facades.

## Technical Context

- Backend: PHP 8.5, Laravel 13, existing MySQL/Eloquent domain model.
- API: `/api/v1/cups`, optional API authentication, existing `Slice` pagination.
- Frontend: Vue 3 + TypeScript + PrimeVue, existing SPA router, `ListingTable` and filter components.
- Localization: existing `resources/lang/*` and SPA i18n dictionaries.

## Architecture and Data Flow

1. `SearchCupDto` validates and normalizes year, name, visibility and pagination input.
2. `ListCupsAction` creates `ListCup` and calls `ListCupService`.
3. The service passes the command's `Criteria` to `CupRepository` and maps
   results with a dedicated lightweight assembler/DTO.
4. The repository applies active, year, name and visibility constraints and returns a paginated
   slice without loading cup events/points.
5. SPA `CupsPage.vue` loads years and the list, builds query parameters, renders filters/table/
   pagination, and exposes authenticated visibility/action columns.

## File Structure

### Backend

- `app/Application/Dto/Cup/SearchCupDto.php`
- `app/Application/Dto/Cup/ViewCupDto.php`
- `app/Application/Service/Cup/ListCup.php`
- `app/Application/Service/Cup/ListCupService.php`
- `app/Domain/Cup/CupRepository.php`
- `app/Infrastructure/Laravel/Eloquent/Cup/EloquentCupRepository.php`
- `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/ListCupsAction.php`
- `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`

### Frontend

- `resources/spa/pages/cups/CupsPage.vue`
- `resources/spa/pages/cups/cupModels.ts`
- `resources/spa/api/cups.ts`
- `resources/spa/api/types.ts`
- `resources/spa/router/index.ts`
- `resources/spa/components/navigationModels.ts`
- `resources/spa/i18n.ts` and locale resources

## Compatibility and Cleanup

- Reuse `ViewCupDto` with authenticated groups, keeping `CupAssembler`, detail routes, mutation routes and export routes because
  they still have callers outside this listing.
- Remove only `ShowCupsListAction`, legacy listing command/service, `CupSearchDto` and listing-only
  web route/tests once the new API listing is covered.
- Update navbar/navigation tests and route regression tests to use `/app/cups`.

## Verification Strategy

- Red/green: add Application criteria/policy tests, API auth/filter contract tests, then implement.
- SPA component/API-model tests cover initialization, debounce search, year/visibility changes,
  pagination reset, loading/error/empty states and links.
- Run focused PHP and frontend tests after each slice; run final `composer cs`, `composer stan`,
  frontend typecheck/tests and `git diff --check` once at the end.
