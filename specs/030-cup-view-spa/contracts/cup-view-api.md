# API Contract: Cup View SPA

## `GET /api/v1/cups/{cupId}`

- Available to guests and authenticated Bearer clients.
- Returns the existing `ViewCupDto` payload.
- `created` and `updated` are included only for authenticated clients.
- Missing cup returns the existing 404 application error.

## `GET /api/v1/cups/{cupId}/events`

Query parameters:

| Parameter | Type | Rule |
|---|---|---|
| `name` | string | Optional, trimmed, 3–255 characters |
| `date` | string | Optional `YYYY-MM-DD` |
| `eventIds[]` | integer[] | Optional positive linked event IDs |
| `page` | integer | Optional, minimum 1 |
| `perPage` | integer | Optional, 1–100; SPA default is 50 |

Response body is a JSON array of existing `ViewCupEventDto` objects. Pagination
uses the standard `X-Current-Page`, `X-Per-Page`, and `X-Has-Next` headers.

Each item contains `id`, `cupId`, `eventId`, and `points`; `created` and
`updated` appear only for authenticated clients. Results always belong to the
route cup ID and are ordered by event ID then stage ID. The SPA resolves the
returned IDs separately through `GET /api/v1/events?ids[]=` with
`withCompetition=1`.
