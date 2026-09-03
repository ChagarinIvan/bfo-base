# Research: удаление legacy competition-раздела

## Inventory findings

- The target SPA already contains list, details, create and edit pages under
  `resources/spa/pages/competitions/`.
- Vue Router already maps the canonical paths `/app/competitions`,
  `/app/competitions/:id`, `/app/competitions/create` and `/app/competitions/:id/edit`.
- Existing V1 competition API routes and Application/Domain services are required by the SPA and
  must remain.
- Current production links found in the repository point to `/app/competitions...`; any remaining
  `/competitions` matches must be classified as API paths, tests asserting intentional 404 behavior,
  or stale legacy references before editing.
- Event, group, person, flag and cup pages contain competition links that are part of the
  transitional migration and must remain functional.
- `resources/views/events/show.blade.php` still imports the removed
  `App\\Bridge\\Laravel\\Http\\Controllers\\Competition\\ShowCompetitionAction`; this is a
  stale legacy reference to remove while preserving the event page.

## Decisions

### Use the existing SPA as the target

**Decision**: Reuse the existing competition SPA and add a page only if an inventory check proves a
required screen is missing.

**Rationale**: The target list/detail/create/edit flow already exists and is covered by router,
page, API and component tests. Creating a second SPA would violate the single-interface goal.

**Alternatives considered**: Keep Blade pages as compatibility shells; rejected because they leave
two competing UIs and make legacy cleanup incomplete.

### Clean legacy presentation as a complete unit

**Decision**: Remove old competition route registrations, web actions, Blade views, stale imports,
and tests that only protect removed behavior together, after searching all usages.

**Rationale**: Partial deletion leaves dead entry points or compile-time references. A usage
inventory makes removal safe and distinguishes legacy presentation from retained API/domain code.

**Alternatives considered**: Hide old pages behind redirects; rejected by default because the
feature's goal is to remove old routes. A redirect is allowed only if a separate explicit contract
requires bookmark compatibility.

### Preserve non-migrated sections and data

**Decision**: Keep event/protocol/cup functionality and all competition persistence/API behavior.
Update only their internal competition links to canonical SPA paths where needed.

**Rationale**: This is a presentation migration, not a data deletion or full section migration.

**Alternatives considered**: Remove all files containing the word `competition`; rejected because
those files often belong to retained sections or API/domain behavior.

## Validation approach

1. Build a repository-wide list of old competition routes, actions, views, imports and URLs.
2. Classify each match as removable legacy, retained API/domain, canonical SPA, retained related
   section, or intentional regression assertion.
3. Confirm the target SPA exists and its canonical routes/tests pass.
4. Delete only the removable set and add/update regression checks for route absence and preserved
   links.
5. Run the feature quickstart, then the repository quality gates at the end of the feature.

## Implementation result (2026-09-03)

- The existing SPA was verified as complete for list, details, create and edit; no new SPA screen
  was required.
- `php artisan route:list --path=competitions` reports only the five intended `/api/v1/competitions`
  routes; no legacy web competition route is registered.
- Removed the stale `ShowCompetitionAction` import from `resources/views/events/show.blade.php`.
- Added regression coverage for absent legacy presentation directories and the stale import in
  `tests/Feature/Competition/CompetitionNavigationTest.php`.
- Existing retained event, group, person, flag and cup links target `/app/competitions...`; V1 API,
  Application/Domain code and persisted data were not changed.
- Focused SPA checks and the full `composer test` suite pass; the full suite completed with 376
  tests, 2845 assertions and two PHPUnit notices.
