# Data model and navigation model

This feature changes navigation references and adds no persisted data.

## Competition route targets

| Target | Visibility | Data source | Canonical path |
|---|---|---|---|
| List | Public | `GET /api/v1/competitions` | `/app/competitions` |
| Details | Public | existing competition V1 view API | `/app/competitions/{id}` |
| Create | Authenticated | `POST /api/v1/competitions` | `/app/competitions/create` |
| Edit | Authenticated | `PUT /api/v1/competitions/{id}` | `/app/competitions/{id}/edit` |

## Legacy entry point states

- **Removed**: equivalent SPA route exists and no usages remain.
- **Redirected**: old URL only sends users to canonical SPA and renders no Blade view.
- **Retained transition**: still serves a non-migrated event/protocol flow, with reason documented.

## Invariants

- API payloads and competition business rules are unchanged.
- Authenticated SPA routes remain protected by router guard and API middleware.
- Non-competition routes and links remain functional.
