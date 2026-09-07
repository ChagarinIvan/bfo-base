# Plan: SPA Person Forms

## Decisions

- Reuse `AddPersonService` and `UpdatePersonInfoService` through thin JSON Bridge actions.
- Reuse `ViewPersonAction` for edit initial data and `ListAllClubAction` for club options.
- Create one `PersonForm.vue`, with separate create/edit pages following `ClubForm`.
- Remove Blade form actions, templates, routes and controller tests after route/link migration.

## Files

- API person create/update actions and V1 routes
- `resources/spa/api/persons.ts`, `types.ts`, router
- `PersonForm.vue`, `CreatePersonPage.vue`, `EditPersonPage.vue` and Vitest coverage
- legacy Person create/edit Bridge/controllers/views/routes/tests

## Constitution Check

Bridge constructs existing commands; Application retains orchestration; Domain factory and aggregate update preserve events. No new legacy service or repository is introduced.
