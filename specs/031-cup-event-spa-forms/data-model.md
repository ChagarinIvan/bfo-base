# Data Model

## CupEvent form input

| Field | Type | Rules |
|---|---|---|
| `eventId` | integer | required, existing active event |
| `points` | numeric | required |

## Invariants

- A stage belongs to the `cupId` route segment; update/view reject a stage of
  another cup.
- Creation rejects duplicate active cup/event pairs.
- Create/update write an authenticated impression and emit existing aggregate
  events for cache invalidation.

## Relationships

`Cup 1—N CupEvent N—1 Event`; the form fetches the cup year and uses the
existing paginated Event endpoint for options.
