# Tasks: SPA Person Rank History

All tasks are complete after the final refactor to the separate rank-history query.

## Backend

- [x] T001 Verify the existing Person Info nested route and rank activation services.
- [x] T002 Keep ViewPerson compact and add the non-paginated person rank-history query.
- [x] T003 Map rank history from its own persisted fields without event/competition names.
- [x] T004 Add the public person rank-history API and regression coverage.
- [x] T005 Add event filtering by `ids[]` and batch loading of competition names.
- [x] T006 Preserve authenticated activation/update JSON actions and one-hour rank catalog caching.

## SPA

- [x] T007 Request rank histories separately from the ranks tab.
- [x] T008 Batch-load referenced events and competitions by IDs.
- [x] T009 Remove rank-history filters and the unused year-catalog request.
- [x] T010 Render rank groups with validity dates and reverse-chronological details.
- [x] T011 Render detail links, anonymous read-only state and authenticated activation controls.
- [x] T011a Display the localized qualification type without labelling lower qualifications as downgrades.
- [x] T012 Cover the API helper, nested route, loading state, grouped details and mutation UI with Vitest.

## Cleanup and verification

- [x] T013 Remove old rank Blade controllers, templates, routes and obsolete tests.
- [x] T014 Update spec, data model, research, contract and quickstart to the separate-query design.
- [x] T015 Run focused tests, full backend/frontend checks, static analysis, Rector dry-run and diff check.
- [x] T016 Render distinct rank periods and keep every lower-rank entry in its own rank group without changing its existing type, with SPA regression coverage in `resources/spa/pages/persons/PersonRanksPage.vue` and `resources/spa/pages/persons/PersonRanksPage.test.ts`.
- [x] T017 Keep period start and end dates in the collapsed rank row only; omit the duplicate columns from expanded execution rows with SPA coverage.
