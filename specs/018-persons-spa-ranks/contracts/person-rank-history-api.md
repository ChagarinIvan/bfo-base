# Person Rank History API Contract

Base path: /api/v1.

## List person rank history

GET /persons/{personId}/rank-histories is public.

The response contains all history records for the active person without pagination, ordered by `achievedOn` descending and then ID descending. Unknown or inactive persons return `[]`. Each item contains only its own persisted fields:

{
"id": "501",
"personId": "42",
"protocolLineId": "901",
"distanceId": "77",
"eventId": "12",
"competitionId": "3",
"rankId": 7,
"changeType": "completion",
"achievedOn": "2024-06-01",
"activatedOn": "2024-06-15",
"startedOn": "2024-06-15",
"finishedOn": "2026-06-15"
}

The SPA resolves rank labels from `GET /ranks` and event/competition labels from `GET /events?withCompetition=1&ids[]=...`.

## Events by IDs

GET /events?withCompetition=1&ids[]={eventId}&ids[]={eventId}

The endpoint returns active events matching the supplied IDs and includes `competitionName` when `withCompetition=1`.

## Activate a rank

POST /person-rank-history/{protocolLineId}/activation

Authentication: required. Body: {"date":"2024-06-15"}. Response: 200 {"personId":"42"}.

## Update or remove activation

PUT /person-rank-history/{protocolLineId}/activation

Authentication: required. Body accepts a new date or {"date":null} to remove activation. Response and errors match activation.

## Rank catalog

GET /ranks is public and returns the existing ordered {id,label} array. The SPA caches this response for 3600 seconds.
