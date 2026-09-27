# V1 routes and export contract

| Method | Path | Auth | Success |
| --- | --- | --- | --- |
| `DELETE` | `/api/v1/cups/{cupId}` | Editor | `204` |
| `DELETE` | `/api/v1/cup-events/{cupEventId}` | Editor | `204` |
| `POST` | `/api/v1/cups/cache-clear` | Editor | `204` |
| `GET` | `/api/v1/cups/{cupId}/export` | Authenticated | CSV attachment |

- Existing `/api/v1/cups/{cupId}/tables/{groupId}` JSON response remains the table view contract.
- `401` for guests and `404` for unknown cup or stage use the established V1 JSON error shape. The existing JSON table view retains its group validation.
- CSV uses `text/csv; charset=UTF-8`, semicolon separators, CRLF records, and quoted fields when a value contains delimiter, quote, or newline. The attachment name includes cup identity.
- Full export contains one section per supported group. Each section starts with group name and a header row, followed by all ranked rows. It includes stage values and total points consistent with the JSON table.
- Empty group sections retain their group name and header.
- The former group export URL has no V1 replacement and returns `404`.
- Cache clear flushes the shared `cups` tag for every cup, including inactive cups; the old cup-specific cache-clear URL returns `404`.
- `/` redirects to `/app/competitions`. Old `/cups/*` action URLs are absent and have no side effects.
- Existing `GET /api/v1/users` stays protected by V1 authentication and returns a direct JSON array containing only `id`, `name`, and `email`. SPA impressions use the email and refresh a stale cached list on an unknown author ID.
