# Data Model: SPA-страница персонов

## PersonListRow

Read-only projection of an active person used by both SPA tables.

| Field | Type | Required | Notes |
|---|---|---:|---|
| `id` | string | yes | Identifier for legacy links |
| `lastname` | string | yes | Surname |
| `firstname` | string | yes | Given name |
| `birthday` | string/null | no | Full birth date in `Y-m-d` format |
| `rankId` | integer | yes | Materialized current rank; `0` means without rank |
| `clubId` | string/null | no | Present only when a club relation exists |
| `created` | Impression/null | auth-only | Existing compact audit projection |
| `updated` | Impression/null | auth-only | Existing compact audit projection |

The response does not include club names, event counts, protocol lines, payments, citizenship or rank history. Those remain
legacy detail/form concerns.

## PersonSearchCriteria

| Field | Type | Semantics |
|---|---|---|
| `name` | string/null | Trimmed, case-insensitive partial match on lastname OR firstname; 3–255 chars when non-empty |
| `rankId` | integer/null | Exact materialized current rank, including `0` |
| `birthYear` | integer/null | Exact year, 1920 through current year |
| `clubId` | integer/null | Exact active club id; missing/inactive club returns empty result |
| `page` | integer | 1-based, default 1 |
| `perPage` | integer | Default 20, bounded by current API maximum 100 |

All non-null filters combine with `AND`. Results contain active persons only and use deterministic
`lastname`, `firstname`, `id` ordering. A person may have no club; that does not remove the row from
the unfiltered list.

## ClubOption

Active-club option returned by `GET /api/v1/clubs/all`:

| Field | Type | Required |
|---|---|---:|
| `id` | string | yes |
| `name` | string | yes |

No `personsCount`, audit fields or person collection are included. Options are sorted by `name,id`
and may be cached for the SPA session. The paginated `/api/v1/clubs` listing is not cached.

## Shared PersonTable contract

`PersonTable` consumes `PersonListRow[]`, pagination metadata, authenticated state, and optional user
impressions. It renders the same name, club, birth-year, rank and action columns in the
global list and club details. Names use `/persons/{id}/show`; club names use `/app/clubs/{clubId}`;
authenticated edit/delete actions use the existing legacy routes. Null club/birth-year values are
empty text rather than broken links.

## Lifecycle and compatibility

- `/app/persons` is the canonical SPA listing route.
- `/persons` is unregistered and returns 404.
- `/persons/{id}/show`, `/persons/create`, `/persons/{id}/edit`, payments, prompts and rank routes
  remain available under their existing auth rules.
- `/api/v1/persons` remains optional-auth public; `/api/person` and `/api/persons` are removed.
