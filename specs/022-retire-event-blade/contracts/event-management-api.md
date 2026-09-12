# Event Management API Contract

All endpoints below require the existing API authentication middleware. Validation failures return the existing `422` field-error format; invalid protocol content returns `400` with error code `invalid_protocol`; unknown or inactive resources return `404`.

## Create event

`POST /api/v1/competitions/{competitionId}/events`

The route `competitionId` identifies the target competition. Multipart fields contain `name`, `description`, `date`, and exactly one of `protocol` (file) or `url`; `competitionId` is not a body field.

Response: `201` with `ViewEventDto`.

## Update event

`PUT /api/v1/events/{eventId}`

Multipart fields: `name`, `description`, `date`, and optionally exactly one replacement source: `protocol` or `url`.

Response: `200` with `ViewEventDto`.

## Deactivate event

`DELETE /api/v1/events/{eventId}`

Response: `204`.

## Unite events

`POST /api/v1/competitions/{competitionId}/events/unite`

JSON body (the competition is identified by the route `competitionId`):

```json
{ "eventIds": ["12", "13"] }
```

Response: `201` with the combined `ViewEventDto`.

## Compatibility

Existing `GET /api/v1/events`, `GET /api/v1/events/{eventId}`, `/app/events/{eventId}`, and all Cup routes remain unchanged. The old `/events/...` Web route group has no replacement URL outside the SPA routes above.
