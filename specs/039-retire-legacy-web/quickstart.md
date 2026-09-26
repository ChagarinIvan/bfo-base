# Validation guide

1. Prepare the local application and test database using the project's normal environment.
2. Run focused PHPUnit request tests for the four routes in [routes-and-export.md](contracts/routes-and-export.md). Confirm guest, missing resource, CSV content, cache invalidation, and absence of the retired group export route.
3. Run focused cup scoring and event cleanup tests after removing `DistanceService`.
4. Run focused Vitest tests for the cup page and API client. Confirm actions use V1 calls and downloads still work.
5. Run `php artisan route:list` and check that the five `/cups/*` action routes are absent and the new V1 routes are present. Visit `/` and confirm it reaches `/app/competitions`.
6. Render registration and password email templates. Inspect the remaining `app/Services` inventory in the spec folder.
7. At feature completion, run the repository's final test, static analysis, style, Rector, frontend, startup, and cup export query-count checks required by the constitution.
