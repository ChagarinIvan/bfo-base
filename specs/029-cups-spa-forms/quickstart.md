# Quickstart: SPA Cup Forms

1. Authenticate in the SPA.
2. Open `/app/cups` and select `Дадаць кубак`.
3. Verify the form contains name, type, events count, year, and visibility.
4. Submit invalid values and verify field errors without a new cup.
5. Submit valid values and verify a success toast and return to `/app/cups`.
6. Select `Рэдагаваць` for a cup, change one field, and save.
7. Verify `/cups/create`, `/cups/{id}/edit`, `/cups/store`, and `/cups/{id}/update` are unavailable.
8. Verify cup detail, table, event, export, and delete flows remain available.

Validation commands:

```sh
npm run ci
composer cs
composer stan
composer test
```
