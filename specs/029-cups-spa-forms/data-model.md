2# Data Model: SPA Cup Forms

## Cup form

| Field | Type | Required | Constraints | Notes |
|---|---|---:|---|---|
| `name` | string | yes | max 255 | Cup display name |
| `eventsCount` | integer | yes | 1–100 | Number of cup stages |
| `year` | integer | yes | existing `Year` enum | Available years are supplied by the existing years API |
| `type` | string | yes | existing `CupType` enum | Options are supplied from the domain enum |
| `visible` | boolean | no | boolean | Defaults to true for creation |

## Cup response

The API response reuses `ViewCupDto`: string `id`, `name`, string `eventsCount`, integer `year`, string
`type`, `groups`, boolean `visible`, and authenticated `created`/`updated` impressions.

## State transitions

- Create: validated form payload → active Cup aggregate → serialized response.
- Edit: active Cup aggregate → validated replacement data → updated aggregate with audit impression.
- Missing/inactive identifier: API error; no SPA form mutation.
- Validation failure: HTTP 422 structured field errors; no persistence change.
