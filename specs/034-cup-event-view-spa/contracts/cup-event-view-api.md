# API Contract: Cup Event View SPA

The page composes existing public V1 reads. All fields use camelCase. Inactive
cup events or their inactive cups return the existing application 404 error.

## Card resources

`GET /api/v1/cup-events/{cupEventId}` returns the stage record. Its `cupId` and
`eventId` identify the existing `GET /api/v1/cups/{cupId}` and event lookup
used by the SPA to render the card and eligible group selector.

## `GET /api/v1/cup-events/{cupEventId}/points`

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

The client may omit `name` for an empty value. It must not send one or two
characters: the API returns 422 for a supplied short value, while the SPA shows
its local minimum-length hint without issuing a broad request.

Every SPA client function accepts an optional `AbortSignal`. Cancellation is a
client-side control-flow outcome, not an API error response.
