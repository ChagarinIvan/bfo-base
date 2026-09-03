# Quickstart: SPA-страница персонов

## Prerequisites

- Project dependencies are installed (`vendor/` and `node_modules/`).
- MySQL is running and the test/local database is configured.
- For manual checks, serve Laravel on `127.0.0.1:8000` and build/serve the SPA according to the
  repository instructions. Production-like Nginx must expose `public/spa` for `/app/*`.

## Targeted automated checks

Run while implementing the backend and frontend slices:

```bash
vendor/bin/phpunit tests/Feature/Api/V1/Person tests/Feature/Api/V1/Club
npm run test -- resources/spa
npm run typecheck
```

Expected: combined person criteria, pagination, club payload/options, public/auth serialization,
and no-N+1 checks pass; SPA tests cover query serialization, filters, shared table, route and
authorization states.

## Manual acceptance scenarios

1. Open `/app/persons` as a guest. The active-person table loads; create/edit/delete actions are not
   visible.
2. Enter a partial surname or firstname of at least three characters, then choose rank, birth year
   and active club. Verify all selected conditions apply together and each filter change returns to
   page 1.
3. Verify the year selector contains 1920 through the current year plus a no-filter option. A person
   with no birth year or club renders an empty cell without a broken link.
4. Sign in. Verify `Дадаць асобу` opens `/persons/create`; names open `/persons/{id}/show`; edit and
   delete retain their existing legacy destinations.
5. Open `/app/clubs/{id}`. Verify its persons block uses the same columns, null handling and links
   as the global table, with server-side club pagination.
6. Change filters quickly while requests are in flight. Verify an older response never overwrites
   newer rows; force an API failure and verify an error with retry; verify zero matches show an empty
   state while preserving selected filters.
7. Request `/persons` and confirm HTTP 404. Confirm `/persons/{id}/show`, `/persons/create` and
   `/persons/{id}/edit` still work under their existing authorization rules.
8. Confirm `/api/person` and `/api/persons` are absent. Confirm `/api/v1/persons` accepts the
   documented camelCase filters and `/api/v1/clubs/all` returns all active `{id,name}` options.

See [api-persons.md](contracts/api-persons.md), [api-club-options.md](contracts/api-club-options.md),
[ui-navigation.md](contracts/ui-navigation.md), and [data-model.md](data-model.md) for exact
contracts.

## Final feature gates

At the end of the feature run once:

```bash
composer test
npm run ci
composer cs -- --sequential
composer stan
composer rector -- --dry-run
git diff --check
php artisan route:list
```

Expected: all gates pass; no `/persons` or `/api/person*` listing routes remain, `/app/persons` is
served by the SPA build, and no new N+1 query is present.

## Implementation evidence (2026-09-03)

- `composer test`: 376 tests, 2850 assertions passed; 2 PHPUnit notices.
- `npm ci --ignore-scripts --no-audit --no-fund`: completed; Node 20 emitted engine/deprecation warnings.
- `npm run ci`: 29 Vitest files, 74 tests passed; lint, typecheck, and SPA build passed.
- `composer cs -- --sequential`, `composer stan`, and `composer rector -- --dry-run`: passed.
- `php artisan route:list`: retains `/api/v1/persons`, paginated `/api/v1/clubs`,
  `/api/v1/clubs/all`, and legacy person detail/form/action routes; no `/persons` list or
  non-versioned person API route remains.
- API integration coverage verifies all active-club options, cumulative person filters,
  deterministic pagination, auth serialization, and no per-row queries.
