# Quickstart: Cup Type Icons

1. Run `npm run typecheck`.
2. Run focused Vitest tests for `cupTypeModels`, `CupTypeIcon`, `CupForm`, and
   `CupsPage`.
3. Run `npm run ci`.
4. Open `/app/cups` and verify each cup name has a sport/category icon.
5. Open `/app/cups/create` and `/app/cups/{id}/edit`; verify type options show
   icons and labels and that submitting still sends the enum string.
6. Verify an unknown type renders the fallback icon without a runtime error.
