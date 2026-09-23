# Data Model: Cup Event View SPA

## Existing entities

### Cup event context

| Field | Purpose | Visibility |
|---|---|---|
| id, cupId, points | Stage identity and configured maximum points | Public |
| cup name, year, type, groups | Card context and group options | Public |
| event ID, name, date | Card context and SPA event link | Public |
| competition ID, name | Card context and SPA competition link | Public |

### Calculated standing

| Field | Purpose |
|---|---|
| cupEventId | Parent stage identity |
| place | Position in the calculated group result |
| personId, personName, personYear | Athlete link and identity |
| personClubId, personClubName | Club presentation/link |
| time | Protocol result time or fallback |
| points | Awarded score, including configured maximum score marker |

## Query input

| Field | Source | Rule |
|---|---|---|
| cupEventId | Route | Required positive identifier for an active cup event |
| groupId | Query | Required valid cup group belonging to the cup |
| name | Query | Optional trimmed athlete-name search, 3–255 characters |
| page, perPage | Query | Shared pagination, perPage 1–100; SPA defaults to 50 |

## Relationships

- One cup event belongs to one active cup and one linked event.
- A cup type supplies the cup's eligible groups.
- A selected cup group produces zero or more calculated standings for a cup
  event; every standings row belongs to exactly one selected group.
