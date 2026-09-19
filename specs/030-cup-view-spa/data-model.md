# Data Model: Cup View SPA

## Existing read entities

### Cup (`ViewCupDto`)

| Field | Use on page | Guest visibility |
|---|---|---|
| id, name, year, type, eventsCount, groups, visible | Information card | Public |
| created, updated | Audit details | Authenticated only |

### Cup stage (`ViewCupEventDto`)

| Field | Use on table | Guest visibility |
|---|---|---|
| id, cupId, eventId, points | Identity, actions, points | Public |
| created, updated | Audit details | Authenticated only |

The SPA resolves `eventId` through the existing `Event` list contract with
`withCompetition=1` to display the linked name and date.

## Query input

| Field | Source | Rule |
|---|---|---|
| cupId | Route | Required positive cup identifier |
| name | Query | Optional trimmed string, minimum 3 characters |
| date | Query | Optional `YYYY-MM-DD` date |
| eventIds | Query | Optional list of positive linked event IDs |
| page, perPage | Query | Shared pagination; SPA defaults to 50 |
