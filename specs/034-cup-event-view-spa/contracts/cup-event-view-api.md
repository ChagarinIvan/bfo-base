# API Contract: Cup Event View SPA

Both endpoints are public V1 reads. All fields use camelCase. Inactive cup
events or their inactive cups return the existing application 404 error.

## `GET /api/v1/cup-events/{cupEventId}/context`

Returns one public cup-event context object:

```json
{
  "id": "17",
  "cup": { "id": "4", "name": "Кубак", "year": "2026", "type": "bike", "groups": [] },
  "event": {
    "id": "25",
    "name": "Этап",
    "date": "2026-05-10",
    "competitionId": "9",
    "competitionName": "Спаборніцтва"
  },
  "points": "100"
}
```

## `GET /api/v1/cup-events/{cupEventId}/standings`

Query parameters:

| Parameter | Type | Rule |
|---|---|---|
| `groupId` | string | Required eligible cup-group identifier |
| `name` | string | Optional trimmed athlete name, 3–255 characters |
| `page` | integer | Optional, minimum 1 |
| `perPage` | integer | Optional, 1–100; SPA default is 50 |

The body is a JSON array of standing objects. Standard
`X-Pagination-Current-Page`, `X-Pagination-Per-Page`, and
`X-Pagination-Has-Next` headers describe the page.

Each row contains `cupEventId`, `place`, `personId`, `personName`,
`personYear`, `personClubId`, `personClubName`, `time`, and `points`. The
result order is the established calculated standings order before filtering.
