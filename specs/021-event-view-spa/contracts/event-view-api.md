# Event View API Contract

All endpoints are under `/api/v1` and use the existing optional API authentication middleware unless noted.

## `GET /events/{eventId}`

Returns an active event detail model. Guests receive public fields; authenticated users additionally receive `created` and `updated` impressions.

- `200`: event detail with competition display data. Cup associations are not included.
- `404`: no active event exists.

## `GET /distances?eventId={eventId}`

Returns every distance belonging to an active event, without pagination.

- `eventId`: required positive integer.
- `200`: JSON array of `{ id, eventId, groupName, length, points, disqual }`.
- `404`: no active event exists.
- `422`: invalid or omitted `eventId`.

## `GET /protocol-lines`

Existing paginated endpoint, extended with the following query fields:

| Field | Required | Meaning |
|---|---|---|
| `distanceId` | Required for event-result usage | Positive integer selecting one distance |
| `name` | No | Trimmed athlete first/last-name substring filter |
| `withClub` | No | `1` includes resolved club information |
| `page`, `perPage` | No | Existing pagination convention |

For this feature the response adds `serialNumber`, `club`, `clubId`, `clubName`, `rank`, `points`, `vk`, and `activateRank`. `club` is raw; `clubId` and `clubName` are nullable and are resolved only with `withClub=1`. The event page must not request `withEvent` or `withCompetition`.

- `200`: paginated protocol lines.
- `422`: invalid filters, including an unbounded request without a supported selector.

## SPA navigation

- `/app/events/:eventId` — public event view.
- `/events/{eventId}/edit` — retained legacy authenticated edit form, linked only for authenticated SPA users.
- `/app/protocol-lines/:protocolLineId/person` — existing authenticated person-assignment form, linked from the staff Actions column.
