# Rank Check API Contract

Base path: `/api/v1`. All endpoints require the existing API authentication. In the current
application every authenticated API user is an administrator for this feature; guests receive `401`.

## Create a check

`POST /rank-checks` with multipart field `list`.

Success: `202 Accepted`

```json
{
  "id": "123",
  "status": "PARSING",
  "created": {"at": "2026-09-16T10:00:00Z", "by": "10"},
  "updated": {"at": "2026-09-16T10:00:00Z", "by": "10"}
}
```

Invalid rank-check files return the application `400` error `invalid_rank_check_list`.
Transport validation errors (for example, missing multipart field) return the standard `422` shape.
Unauthenticated users receive `401`.

## View a check

`GET /rank-checks/{rankCheckId}`

The status endpoint returns only the check DTO. Rows are requested separately with
`GET /rank-checks/{rankCheckId}/rows?page=1&perPage=50`; this endpoint uses the standard `Slice`
response and pagination headers. Rows are read from persisted `RankCheckRow` records and are never
all loaded into a single response.

While processing:

```json
{
  "id": "123",
  "status": "PARSING",
  "created": {"at": "2026-09-16T10:00:00Z", "by": "10"},
  "updated": {"at": "2026-09-16T10:00:00Z", "by": "10"}
}
```

Ready:

```json
{
  "id": "123",
  "status": "READY",
  "created": {"at": "2026-09-16T10:00:00Z", "by": "10"},
  "updated": {"at": "2026-09-16T10:00:12Z", "by": "10"}
}
```

`GET /rank-checks/123/rows` returns the standard serialized `RankCheckRowDto[]`; pagination is
provided by `X-Pagination-*` response headers.
For `PARSING` or `FAILED` checks the repository status filter returns no rows.

Failed:

```json
{
  "id": "123",
  "status": "FAILED",
  "updated": {"at": "2026-09-16T10:00:12Z", "by": "10"},
  "error": "Не удалось обработать список разрядов. Свяжитесь с администратором."
}
```

Unknown IDs return the standard `404` response. Since all authenticated users have feature access,
an authenticated user can view the checks available through the API policy; guests receive `401`.
