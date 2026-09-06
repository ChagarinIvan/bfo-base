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

- all rows are grouped by rankId;
- the rank catalog provides the label for each group;
- a group summary displays the rank and confirmation count;
- a group summary displays the earliest startedOn and the latest finishedOn; an unfinished item marks the group as current;
- expanded groups display the detailed history table.

## Mutations and catalog

Activation uses required date; update accepts a date or null. The existing {id, label} rank catalog is cached by the SPA for 3600 seconds.
