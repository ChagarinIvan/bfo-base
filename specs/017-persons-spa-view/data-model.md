# Data Model: SPA View Person

## Person

Existing active person used as page context.

| Field | API form | Use |
|---|---|---|
| id | string | Route and ownership filter |
| lastname, firstname | string | Person Info and protocol table |
| birthday | nullable Y-m-d | Person Info |
| rankId | integer | Person Info |
| clubId | nullable string | Person Info |
| created, updated | impression DTO | Authenticated Person Info |

## ProtocolLine

Existing protocol result row owned by a person through person_id and linked to a distance.

| Field | API form | Use |
|---|---|---|
| id, personId, distanceId, eventId, competitionId | strings | Identity and links |
| firstname, lastname | strings | Participant display |
| competitionName, eventName, groupName | nullable/string | Table display |
| eventDate | Y-m-d | Table and date/year filters |
| year | nullable string | Participant birth year |
| time, place, completeRank | nullable strings | Result columns |

## ProtocolLineResources

Typed read resources:

- withEvent: load event, date and distance group required for event/table data.
- withCompetition: load the competition relation used for competition name and link.

The page requests both flags. The repository must not perform per-row relation queries.

## ProtocolLineSearch

Query input:

- required personId;
- optional four-digit year;
- optional competitionName, minimum three characters when present;
- optional date in Y-m-d;
- optional withEvent and withCompetition boolean flags;
- page and perPage from the shared pagination DTO.

The command exposes Criteria for scalar filters and ProtocolLineResources for relation loading.
