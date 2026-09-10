# Quickstart: Event View SPA

## Prerequisites

- Run the application with its configured MySQL database.
- Use an event containing at least two distances and protocol lines; include a raw club name that normalizes to an existing club.

## Validation

1. Open the competition SPA and follow an event link. Confirm the URL is `/app/events/{eventId}`, event information and cup badges render, and the first distance is selected.
2. Change the distance. Confirm all selector options came from the event, only that distance's lines render, and empty distance results show an empty state.
3. Filter by a first-name fragment and then a last-name fragment. Confirm pagination resets and results remain within the selected distance.
4. Confirm a normalized club match links while an unmatched raw club name remains plain text.
5. As a guest, confirm impressions, Edit, activation date, and Actions are absent.
6. Authenticate. Confirm impressions appear, Edit opens the legacy edit page, and every Assign person control opens the existing SPA assignment form.
7. Confirm `/events/{eventId}` and `/events/d/{distanceId}` are absent from the route table while retained event maintenance routes still exist.

## Automated checks

Run focused PHPUnit tests for Event, Distance, and ProtocolLine V1 actions, then focused Vitest tests for the event API/page/router. Run the final repository quality gates once after all tasks are complete.
