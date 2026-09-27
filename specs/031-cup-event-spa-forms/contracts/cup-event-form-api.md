# Cup Event Form V1 API

All mutations require a Sanctum Bearer token; JSON names are camelCase.

| Method | Path | Request | Response |
|---|---|---|---|
| GET | `/api/v1/cup-events/{cupEventId}` | — | `ViewCupEventDto` |
| POST | `/api/v1/cup-events` | `{ "cupId": 4, "eventId": 12, "points": 100 }` | `201 ViewCupEventDto` |
| PUT | `/api/v1/cup-events/{cupEventId}` | `{ "eventId": 12, "points": 90 }` | `ViewCupEventDto` |

Validation failures return `422`; missing entities return the standard V1
application error; guests receive `401` on mutations.
