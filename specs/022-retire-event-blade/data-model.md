# Data Model: Retire Event Blade

No persistence schema changes are required.

## Event write input

| Field | Rules | Used by |
|---|---|---|
| `competitionId` | Existing active competition identifier from the route /competitions/{competitionId} | Create event |
| `name` | Required, maximum 255 characters | Create and update |
| `description` | Required, maximum 255 characters | Create and update |
| `date` | Required valid date | Create and update |
| `protocol` | Uploaded file, mutually exclusive with URL | Create and optional update |
| `url` | Protocol source URL, mutually exclusive with upload | Create and optional update |

## Unite events input

| Field | Rules |
|---|---|
| `eventIds` | Required array of at least two distinct events belonging to the route competition |

The operation creates one combined event using existing date, group-distance aggregation, participant-time, and placement rules. It does not alter the selected source events.

The unite request locks the selected source events inside the application transaction. The combined event is created and persisted before its distances and protocol lines are generated, because those relations require the new event identifier.

## Protocol processing

EventProtocolDto remains an HTTP/Application input DTO containing only protocol or url. The Application services convert it to the domain ProtocolSource; the domain ProtocolFactory returns a Protocol or raises InvalidProtocolContent. API actions expose that failure as the Application invalid_protocol error with HTTP 400.

Event updates apply event information and protocol replacement independently. They release EventInfoUpdated and EventProtocolUpdated domain events, with separate handlers.

## Event presentation

`ViewEventDto` is the sole event presentation model for SPA reads and the remaining Cup Blade assemblers. It contains identifiers, name, description, date, optional competition name, participant count, and authenticated impressions. Cup views must not require legacy distance/cup arrays.
