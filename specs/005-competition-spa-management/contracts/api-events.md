# API contract: Events V1

**Endpoint**: `GET /api/v1/events?competitionId={competitionId}`
**Access**: public  
**Convention**: direct JSON array; errors use `{ "errors": [...] }`.

## Query

| Parameter | Required | Validation | Meaning |
|---|---:|---|---|
| competitionId | yes | Positive integer | Competition whose active events are requested. |

Missing or invalid parameter returns `422` validation error. Missing/soft-deleted competition returns `404`.

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

Only active events of the requested active competition are returned in date order. All data required by the table and legacy links is included without a query per element. Optional valid Bearer token adds the existing `created` and `updated` impressions.
