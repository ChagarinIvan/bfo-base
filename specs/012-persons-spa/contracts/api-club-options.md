# API Contract: Club options for person filtering

**Endpoint**: `GET /api/v1/clubs/all`

The all-items endpoint is a separate versioned route. The default paginated
`/api/v1/clubs` contract remains unchanged and is not cached by the SPA.

## Request

The endpoint does not require query parameters.

The endpoint remains available through the existing optional-auth middleware. The implementation
returns the complete active-club option set without an artificial item limit.

## Response

HTTP 200 returns a JSON array:

```json
[
  {"id": "17", "name": "Клуб"}
]
```

Semantics:

- active clubs only;
- fields are exactly `id` and `name`;
- stable `name`, then `id` ordering;
- one query with an `id,name` projection, with no per-club queries;
- no pagination headers are returned because the response is not paginated.

The SPA caches this response using the existing ranks/years cache pattern. The paginated
`/api/v1/clubs` request remains uncached.
