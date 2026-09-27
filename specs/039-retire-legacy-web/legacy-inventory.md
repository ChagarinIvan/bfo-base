# Legacy inventory

## Web routes at baseline

`WebRoutesServiceProvider` registers `/`, `GET /cups/{cupId}/cache`, `GET /cups/{cupId}/delete`, `GET /cups/{cup}/export`, `GET /cups/{cup}/{group}/table-export`, and `GET /cups/{cupId}/{event}/delete`. The full export is also registered at `GET /api/v1/cups/{cup}/export`, but it still invokes the browser controller. SPA callers of the old URLs are `CupLayoutPage.vue`, `CupsPage.vue`, `CupViewPage.vue`, and `CupInfoNavigation.vue`.

## Browser view files

`resources/views/layouts/*` and `resources/views/components/*` have no active production renderer after removal of the browser cup controllers. Their PHP counterparts are under `app/Bridge/Laravel/View/Components/`; `ViewProvider` registers them. `Action`, `CupAction`, and `ViewActionsService` serve the browser controllers. `resources/views/emails/registration.blade.php` and `password.blade.php` remain in use by `RegistrationUrlMail` and `PasswordMail`.

The old Laravel Mix entry points are `resources/js/*`, `resources/css/*`, `webpack.mix.js`, `public/js/*`, `public/css/*`, `public/fonts/vendor/*`, and `public/mix-manifest.json`. The SPA uses `resources/spa/*` and Vite. The obsolete Mix scripts and their exclusive npm dependencies are removed from `package.json` and `package-lock.json`; FontAwesome remains because `resources/spa/main.ts` imports it.

## Legacy service classes

| Class in `app/Services` | Production callers at baseline | Disposition |
| --- | --- | --- |
| `CupEventsService` | Two browser CSV controllers | Remove; export uses `CupTableBuilder`. |
| `DistanceService` | Cup types and event cleanup handlers | Remove; cup types use `DistanceRepository` through protected base methods, and event handlers use the deletion port. |
| `UserService` | `ViewActionsService`, `Language` middleware | Remove with browser view layer after checking locale behavior. |
| `ViewActionsService` | Browser `Action` trait | Remove with browser view layer. |
| `ParserService` | `ParseProtocolHandler`, `UpdateEventProtocolHandler` | Retain; parsing is active and migration is larger than route cleanup. |
| `PersonsService` | `ExportPersonsCommand`, `OrientBySyncService` | Retain; command and integration require a separate migration. |
| `ProtocolLineIdentService` | Protocol handlers, identification and backfill commands, rank-check matcher | Retain; ranking and identification require a separate migration. |
| `ProtocolLineService` | Protocol handlers and identification command through `ProtocolLineOperations` | Retain; protocol import requires a separate migration. |

## Other retained files

- `Language` middleware sets the application locale for web requests. It remains as a small middleware using the fixed Belarusian locale after removal of `UserService`.
- `resources/views/emails/*` are mail content, not browser frontend code.
- `app/Repositories/ProtocolLinesRepository` is a separate active cup-scoring dependency. Replacing it would expand this feature into a scoring repository migration.
