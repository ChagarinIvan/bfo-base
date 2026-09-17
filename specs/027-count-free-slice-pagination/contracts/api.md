# API Contract: Count-Free Slice Pagination

## Paginated response

The response body remains a JSON array of the existing listing DTOs. No DTO fields are added solely for pagination.

Required response headers:

| Header | Value |
|---|---|
| `X-Pagination-Current-Page` | 1-based requested/current page |
| `X-Pagination-Per-Page` | Effective validated page size |
| `X-Pagination-Has-Next` | `true` when the bounded read found `perPage + 1` rows; otherwise `false` |

The response MUST NOT include `X-Pagination-Total` or `X-Pagination-Last-Page`.

## Request

Existing `page` and `perPage` query parameters remain supported. Existing listing filter parameters and authorization rules remain unchanged. Invalid page/page-size input continues to use the project’s standard 422 validation response.

## Semantics

- The server reads `perPage + 1` rows using the same criteria, joins, filters and ordering as the public page.
- The extra row is used only to calculate `X-Pagination-Has-Next` and is never serialized.
- Empty results return HTTP 200 with `X-Pagination-Has-Next: false`.
- The contract applies to all current API actions returning the shared Slice, including persons, events, groups, competitions, clubs, person payments, person prompts, protocol lines, rank checks and rank-check rows.
- Non-paginated endpoints and endpoints returning a deliberately bounded reference collection are not changed by this contract.
