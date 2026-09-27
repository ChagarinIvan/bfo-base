# Cup API Contract

All endpoints require the existing authenticated Sanctum V1 middleware.

## Create

`POST /api/v1/cups`

Request JSON:

```json
{
  "name": "Spring Cup",
  "eventsCount": 4,
  "year": 2026,
  "type": "master",
  "visible": true
}
```

Response: `201` with serialized `ViewCupDto`.

## View

`GET /api/v1/cups/{cupId}`

Response: `200` with serialized `ViewCupDto`; missing or inactive cup returns the existing structured
not-found error.

## Update

`PUT /api/v1/cups/{cupId}`

Request uses the same fields as create. Response: `200` with serialized `ViewCupDto`.

Invalid fields return `422` with the shared `errors[]` format and field names matching the camelCase
request names. Unauthenticated requests return `401`.
