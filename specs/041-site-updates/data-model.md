# Data Model: История обновлений сайта

## UpdateEntry

| Field | Type | Rule |
| --- | --- | --- |
| `spec` | string | Unique full specification directory name, including numeric prefix |
| `date` | ISO calendar date | Documentation fixation date, not production release date |
| `title` | string | User-facing Belarusian title |
| `summary` | string | One concise sentence |
| `details` | string | More context, plain text |
| `example` | optional string | Concrete action/outcome when relevant |
| `link` | optional `{label, href, kind}` | `site` for current route, `task` for superseding task |

All entries are immutable source data. Rendering sorts by date descending and full spec slug descending for equal dates; source order does not affect display. There is no user state or database lifecycle.

## Invariants

- Exactly one entry per spec directory, including 041.
- Every `site` link points to a route that exists in the SPA without a placeholder ID.
- Every `task` link identifies a checked task in a later spec and names the replacing change.
- A replaced entry uses a task link, never an obsolete site route.
