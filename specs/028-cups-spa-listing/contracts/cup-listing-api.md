# Cup listing API contract

## GET `/api/v1/cups`

The endpoint is available to anonymous and authenticated users through optional API
authentication.

### Query parameters

| Parameter | Type | Required | Meaning |
|---|---|---:|---|
| `year` | enum integer | no | Cup year; defaults to the current/first available SPA year |
| `name` | string | no | Trimmed partial case-insensitive cup name search |
| `visible` | `1\|0` | no | Visibility flag; omission means all for authenticated users, anonymous requests are forced to `1` |
| `page` | positive integer | no | 1-based page |
| `perPage` | positive integer | no | Existing API page-size bounds |

### Response

HTTP 200 returns the standard project `Slice` JSON array and pagination headers. Each
item contains:

```json
{
  "id": "101",
  "name": "Test master cup",
  "year": 2022,
  "type": "master",
  "groups": [{"id": "M_35_", "name": "M35"}],
  "visible": true,
  "created": {"at": "2022-01-01 10:00:00", "by": "Admin"},
  "updated": {"at": "2022-01-01 10:00:00", "by": "Admin"}
}
```

`created` and `updated` are omitted or empty according to the existing impression DTO
contract for anonymous responses. The response must not include cup events, calculated
points, protocol lines, export data, or result blobs.

### Security and validation

- Anonymous responses contain only `visible=1` records regardless of the submitted
  `visible` parameter.
- Authenticated responses may select `true`, `false`, or `all`.
- Invalid year, visibility mode, page, or page size returns the standard HTTP 422 payload.
