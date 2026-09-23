# Quickstart

1. Keep `storage/tests/2026/20260919.htm` in the test fixtures.
2. Run `php vendor/bin/phpunit --no-progress tests/Models/Parser/AlbatrosTimingParserTest.php`.
3. Verify the fixture case returns 273 rows and representative ordinary,
   incomplete and final-group rows.
4. Run all parser tests, then `composer cs`, `composer stan` and the relevant
   parser checks.
5. Confirm no footer row has entered the result and existing 20260404 and
   other provider cases remain green.
