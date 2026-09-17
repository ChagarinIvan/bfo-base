# Quickstart: Проверка разрядов по списку

## Prerequisites

- Application is running with MySQL, Redis and a queue worker/Horizon.
- An authenticated API user exists; every authenticated API user is an administrator for this feature.
- A fixture contains the six-column input and rows for: matching person, mismatched person,
  missing person, blank line and «Вакансия».

## End-to-end validation

1. Open the SPA as an authenticated user and select «Проверка разрядов».
2. Upload the fixture from `tests/fixtures` through the form.
3. Assert `202` and a response containing `id` and `status: PARSING`.
4. Assert the page displays pending state and calls the view endpoint while processing.
5. Wait for the worker and assert `READY`; verify the result has one row per non-empty,
   non-vacancy input line and the seven historical columns.
6. Request page 1 and the last page; verify pagination metadata, stable position ordering and that
   the API does not return all rows in one response.
7. Verify matching, mismatch and missing-person rows, including source/database values.
8. Repeat with a structurally invalid but accepted CSV and assert `FAILED`, safe error text, no
   partial rows and no more polling. Missing/non-CSV files are rejected before a check is created
   with the documented `400` application error.
9. Leave the page while `PARSING` and assert no further timer requests occur.
10. Run the daily cleanup and verify completed runs/rows/files older than 24 hours are deleted,
    while active and newer runs remain.

## Automated checks

- PHP unit tests for status transitions, parser/result mapping and application orchestration.
- API request tests for `202`, `400`, `401`, status-only view and `READY` rows pagination.
- SPA tests for upload, pending polling, ready table, failed state and unmount cleanup.
- Final project gates from the constitution: `composer test`, `composer stan`, `composer cs`,
  Rector dry-run, frontend typecheck/test/build and `git diff --check`.
