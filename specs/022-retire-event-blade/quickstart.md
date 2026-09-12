# Quickstart Validation: Retire Event Blade

## Prerequisites

- Local MySQL/Redis stack is running.
- SPA dependencies are installed.
- Use an authenticated account for mutation scenarios.

## API validation

1. Create an event for a competition using a multipart upload; verify `201`, returned event fields, and subsequent event view.
2. Repeat creation with an OBelarus URL rather than a file.
3. Update the event with and without a replacement protocol source; verify field validation on invalid input.
4. Submit an invalid or unreachable protocol source and verify the API returns `400` with `invalid_protocol`.
5. Unite two events from the same competition; verify `201`, a new event, and unchanged source events. Verify fewer than two IDs and an ID from another competition are rejected.
6. Delete an event and verify `204` plus its absence from active event lists.
7. Repeat a protected mutation as a guest and verify authentication rejection.

## SPA validation

1. Open a competition details page as an organiser. Create-event and unite-event actions use `/app` routes and use existing action-button styling.
2. Create and edit an event. Form fields, upload/URL selection, local/server validation, loading, toast, and redirect match existing SPA management pages.
3. Open an event as an organiser. The deactivate control has an icon, normal severity colour, and destructive confirmation.
4. Verify direct legacy `/events/...` routes resolve as not found.
5. Open Cup list and Cup event pages. Their existing Blade appearance and event/competition links still work.

## Automated checks

Run targeted PHPUnit tests for the new Event API actions and unite command, targeted Vitest tests for Event forms/routes/clients, then one final `composer test`, `composer stan`, `composer cs`, `composer rector -- --dry-run`, and `npm run ci`.
