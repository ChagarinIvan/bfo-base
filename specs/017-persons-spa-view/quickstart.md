# Quickstart: SPA View Person

## Prerequisites

- project dependencies installed;
- test database available;
- frontend dependencies installed.

## Backend validation

Run the focused API and application tests:

    vendor/bin/phpunit tests/Application/Service/ProtocolLine tests/Feature/Api/V1/ProtocolLine --fail-on-notice

Expected coverage:

- active person returns paginated protocol lines;
- withEvent=1 and withCompetition=1 return table context;
- year, competition name and date filters are applied;
- unknown/inactive person returns 200 with an empty list;
- missing/malformed person and filters return 422;
- query-count test detects per-row relation loading.

## SPA validation

    npm run test -- --run resources/spa/pages/persons/PersonViewPage.test.ts resources/spa/api/protocolLines.test.ts
    npm run typecheck

Expected UI behavior:

1. Open /app/persons/{personId}.
2. Confirm the common Person Info module appears above the four actions.
3. Confirm the participation table and all three filters.
4. Change filters and pagination; only the current result remains visible.
5. Confirm empty, API error and unknown-person states.

## Final checks

    composer cs
    composer stan
    composer rector -- --dry-run
    npm run ci
    git diff --check
