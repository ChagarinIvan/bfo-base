# Quickstart

1. Sign in and open `/app/cups/{cupId}`.
2. Select **Дадаць этап**, choose an event, enter points and save.
3. Confirm the returned cup page lists the new stage; edit it and verify the
   changed points.
4. Confirm old create/edit form URLs are no longer registered.
5. Validation on 2026-09-20: `npm run ci`, `composer cs`, `composer stan` and
   `composer rector` passed. Backend request tests were attempted but local
   MySQL was unavailable (connection refused on ports 3306 and 13306); rerun
   `composer test` once the MySQL service is started.
