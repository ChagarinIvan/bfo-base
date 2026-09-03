# API Contract: Persons listing

**Endpoint**: `GET /api/v1/persons`

The endpoint is public under the existing optional-authentication policy. It is the only API source
for the global SPA persons list and the paginated persons block on club details.

## Query parameters

| Parameter | Type | Required | Meaning |
|---|---|---:|---|
| `name` | string | no | Trimmed partial case-insensitive match in lastname or firstname; 3–255 chars |
| `rankId` | integer | no | Exact current rank id, including `0` |
| `birthYear` | integer | no | Exact year from 1920 through current year |
| `clubId` | integer | no | Exact active club id |
| `page` | integer | no | 1-based page, default 1 |
| `perPage` | integer | no | Default 20, bounded by server (maximum 100) |

All supplied filters are cumulative. Blank `name` is omitted. Invalid integer/rank/year values use
the existing V1 validation error shape with HTTP 422. A missing or inactive `clubId` returns HTTP
200 with an empty array, not a server error.

## Response

HTTP 200 returns a JSON array of [PersonListRow](../data-model.md#personlistrow):

```json
[
  {
    "id": "7",
    "lastname": "Іваноў",
    "firstname": "Ян",
    "birthday": "2001-06-04",
    "rankId": 6,
    "clubId": "17"
  }
]
```

Authenticated responses may include `created` and `updated` according to existing serializer groups;
public responses omit them. Pagination is communicated through:

- `X-Pagination-Current-Page`
- `X-Pagination-Per-Page`
- `X-Pagination-Total`
- `X-Pagination-Last-Page`

## Performance invariant

The response includes club display data and event count without per-row follow-up HTTP or SQL
requests. The query returns active persons in deterministic lastname/firstname/id order.
