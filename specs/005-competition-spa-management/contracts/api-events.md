# API contract: Events V1

**Endpoint**: `GET /api/v1/events?competitionId={competitionId}`
**Access**: public  
**Convention**: direct JSON array with standard `X-Pagination-*` headers; errors use `{ "errors": [...] }`.

## Query

| Parameter | Required | Validation | Meaning |
|---|---:|---|---|
| competitionId | yes | Positive integer | Competition whose active events are requested. |
| page | no | Positive integer | Page number, default `1`. |
| perPage | no | Positive integer, maximum `20` | Items per page, default `20`. |

Missing or invalid parameter returns `422` validation error. A missing or soft-deleted
competition returns the same empty array as a competition without active events.

## Response 200

```json
[
  {
    "id": "17",
    "competitionId": "42",
    "name": "Middle distance",
    "description": "Final",
    "date": "2026-05-10",
    "participantsCount": 85
  }
]
```

Only active events belonging to an active competition are returned in date order. All data required
by the table and legacy links is included without a query per element. Optional valid Bearer token
adds the existing `created` and `updated` impressions.

Pagination metadata is returned through the same `X-Pagination-Current-Page`,
`X-Pagination-Per-Page`, `X-Pagination-Total`, and `X-Pagination-Last-Page`
headers as the competitions list.
