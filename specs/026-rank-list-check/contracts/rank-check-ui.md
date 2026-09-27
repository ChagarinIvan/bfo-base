# Rank Check UI Contract

- Navigation shows «Проверка разрядов» only to an authenticated API user.
- The upload page accepts one supported list file and disables submission while the create request
  is in flight.
- After `202`, the page navigates to the created check and renders `PARSING` with a spinner/message.
- While `PARSING`, the page requests `GET /rank-checks/{id}` no less often than every 5 seconds;
  the timer is cancelled on `READY`, `FAILED`, route change, or component unmount.
- `READY` renders the seven historical columns and makes source/database differences visible;
  the table requests server pages using the existing SPA listing pagination conventions.
- `FAILED` renders a localized safe error and no partial rows.
- Network errors during polling show a non-terminal warning and do not disable the next retry.
