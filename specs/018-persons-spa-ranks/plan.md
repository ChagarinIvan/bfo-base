# Implementation Plan: SPA Person Rank History

## Constitution Check

- Application/Domain/Bridge/Infrastructure boundaries are preserved.
- Rank history is an existing Person relation; no new repository or list use case is introduced.
- View Person receives typed eager-loading resources from the Application command.
- Mutation API actions reuse existing Application services and transactions.
- Unit/API/SPA tests cover the changed behavior.

## Architecture

### Backend

1. Keep ViewPerson lightweight and expose rank history through a separate non-paginated list endpoint scoped by person ID.
2. Map rank history from its own persisted fields only; do not resolve event or competition relations in the history DTO.
3. Extend the events API with an `ids[]` filter and let the SPA enrich history rows with event and competition names in one batch.
4. Keep authenticated activation/update actions and the public rank catalog; cache the catalog only in the SPA.

### Frontend

1. Keep ranks as a child of the Person Info layout.
2. Request rank history separately and request referenced events with `withCompetition=1&ids[]=...`.
3. Return histories in descending achievement-date order and render a flat reverse-chronological timeline with links and activation actions.
4. Keep anonymous/authenticated behavior.

### Cleanup

Delete the old rank Blade controllers/templates/routes/tests and remove the obsolete standalone history-list contract. Retain rank calculation, rebuild jobs and activation domain behavior.

## Files

Backend:

- app/Application/Service/Person/ViewPerson.php
- app/Application/Service/Person/ViewPersonService.php
- app/Application/Dto/Person/ViewPersonDto.php
- app/Application/Dto/Person/PersonAssembler.php
- app/Application/Service/PersonRankHistory/ListPersonRankHistoryService.php
- app/Bridge/Laravel/Http/Controllers/Api/V1/PersonRankHistory/ListPersonRankHistoryAction.php
- app/Domain/Person/PersonResources.php
- app/Infrastructure/Laravel/Eloquent/Person/EloquentPersonRepository.php
- app/Bridge/Laravel/Http/Controllers/Api/V1/Person/ViewPersonAction.php
- app/Application/Dto/Event/SearchEventDto.php
- app/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepository.php

Frontend:

- resources/spa/api/persons.ts
- resources/spa/api/personRankHistory.ts
- resources/spa/pages/persons/PersonRanksPage.vue
- resources/spa/api/types.ts

## Verification

Run focused backend and Vitest tests after the refactor. At feature completion run the full backend suite, frontend CI, CS, PHPStan, Rector dry-run and diff check.
