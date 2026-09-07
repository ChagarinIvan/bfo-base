# Data Model: SPA Person Rank History

## ViewPerson

The person response remains compact:

GET /api/v1/persons/{personId}

It does not contain rank histories. The ranks tab loads them separately.

## PersonRankHistory

Existing persisted record; no schema change:

id, person_id, protocol_line_id, distance_id, event_id, competition_id, rank, change_type, achieved_on, activated_on, started_on, finished_on.

The list API DTO contains only persisted history fields:

id, personId, protocolLineId, distanceId, eventId, competitionId, rankId, changeType, achievedOn, activatedOn, startedOn, finishedOn.

The list query is scoped through the active person and eager-loads only the relation needed to obtain the history rows. Event and competition labels are resolved by a separate batch request:

GET /api/v1/events?withCompetition=1&ids[]=...

## Frontend group model

- API returns rows ordered by achievedOn descending and ID descending as a stable tie-breaker;
- the SPA groups rows by rankId in the order of their latest row;
- expanded groups preserve reverse chronology and show the rank label, localized qualification type, dates and links to events and competitions.

## Mutations and catalog

Activation uses required date; update accepts a date or null. The existing {id, label} rank catalog is cached by the SPA for 3600 seconds.
