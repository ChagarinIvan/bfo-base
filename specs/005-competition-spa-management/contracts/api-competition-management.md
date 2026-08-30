# API contract: Competition management V1

**Base path**: `/api/v1`  
**Convention**: single resource is a direct JSON object; collections are direct JSON arrays; errors use `{ "errors": [...] }`.

## GET /competitions

Public paginated list of active competitions.

| Parameter | Required | Validation | Meaning |
|---|---:|---|---|
| year | no | Existing system year | Defaults to current year. |
| name | no | Trimmed 3–255 characters | Case-insensitive name fragment. |
| date | no | `YYYY-MM-DD` | Inclusive date inside `from`–`to`. |
| page | no | Positive integer | Page number. |
| per_page | no | Existing pagination limit | Page size. |

All supplied filters combine. A short non-empty `name` returns `422` validation error. Response is `ViewCompetitionDto[]` and preserves:

```text
X-Pagination-Total
X-Pagination-Per-Page
X-Pagination-Current-Page
X-Pagination-Last-Page
```

With optional valid Bearer token items also contain `created` and `updated` impressions.

## GET /competitions/{competitionId}

Public single active competition. Response `200` is direct `ViewCompetitionDto`; optional valid Bearer includes impressions. Missing or soft-deleted record returns `404` error envelope.

## PUT /competitions/{competitionId}

Requires `Authorization: Bearer {token}`.

```json
{
  "name": "Championship",
  "description": "Forest sprint",
  "from": "2026-05-10",
  "to": "2026-05-11",
  "mass": false
}
```

All fields follow create validation; `to` must be on or after `from`. Response `200` is direct updated `ViewCompetitionDto`, including authenticated update impression. Errors: `401`, `404`, `422`.

## DELETE /competitions/{competitionId}

Requires `Authorization: Bearer {token}` and performs soft delete only. Response `204` has no body. The record no longer appears in normal list/read endpoints. Errors: `401` or `404`.
