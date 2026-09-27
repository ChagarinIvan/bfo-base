# Quickstart: Count-Free Slice Pagination

## Prerequisites

- PHP 8.5, Composer dependencies, Node dependencies, and a running MySQL 8.4 test database.
- A checked-out branch with the feature artifacts and migrated test schema.
- Representative fixture data containing at least 21 matching rows for a page size of 20, plus empty and exact-page-size cases.

## Core regression proof

Run the shared Slice/adapter test. It must prove all of the following in one test:

1. `perPage = 20` causes a single bounded fetch of 21 rows.
2. No `COUNT` operation/query is called.
3. Only 20 rows are exposed to iteration and JSON serialization.
4. `X-Pagination-Has-Next` is `true`.

Repeat the fixture with 20 and 0 matching rows to verify `hasNext = false`.

## API validation

For one representative endpoint, request page 1 and the next page with `perPage=20`. Verify the body remains an array and headers contain Current-Page, Per-Page and Has-Next. Verify Total and Last-Page are absent. Repeat for filtered relationship queries and rank-check rows.

## Listing matrix validation

Run the listing-specific API/integration tests for every paginated repository. For each supported filter and relevant filter combination, verify:

- expected unique records and stable ordering;
- no duplicate root records after joins;
- correct optional relations/aggregates;
- no new N+1 queries;
- page boundary behavior and filter reset behavior in SPA tests.

## Performance validation

Capture baseline and after measurements for heavy listings using representative data: first-page and filtered-page latency, SQL query count, and database explain plan. Confirm no listing response invokes a total count. Add indexes only when the before/after evidence improves the agreed target without a regression in the matrix.

## Final project gates

At feature completion run the project-required `composer cs`, `composer stan`, `composer rector`/dry-run review, `composer test`, frontend typecheck/tests, and `git diff --check`. Start the application and verify the representative API request manually.
