# Data Model: Event View SPA

## Read Models

### Event detail

| Field | Source | Visibility |
|---|---|---|
| `id`, `competitionId`, `name`, `description`, `date` | Event | Public |
| `competitionName` | Event competition | Public |
| `created`, `updated` | Event impressions | Authenticated only |

### Distance list item

| Field | Source | Notes |
|---|---|---|
| `id`, `eventId` | Distance | Must belong to requested event |
| `groupName` | Distance group | Selector label |
| `length`, `points`, `disqual` | Distance | Displayed beside selector/table |

### Protocol-line list item

| Field | Source | Notes |
|---|---|---|
| identity/result fields | Protocol line | Includes serial number, names, year, rank, time, place, complete rank, points, VK, activation date |
| `personId` | Protocol line | Nullable; controls person link only |
| `club` | Raw protocol-line club value | Always returned and never rewritten |
| `clubId`, `clubName` | Matched active club | Nullable; present only when club resource requested and normalized names match |

## Query Rules

- `eventId` identifies an active event for detail and distance reads.
- `distanceId` restricts protocol lines to one distance; the SPA only offers IDs returned for its event.
- Event-distance protocol lines are ordered by non-null place, then non-null time, then id, all ascending.
- The event result view requests 100 rows initially and supports a distanceId query plus a protocol-line fragment for focused navigation.
- `name` matches athlete first or last name case-insensitively; blank input is ignored.
- Club matching normalizes both raw line name and persisted club name via the existing `ClubNameNormalizer`.
- No lifecycle transition or schema migration is introduced.
