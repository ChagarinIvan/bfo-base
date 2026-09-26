# Data model: Legacy Web Retirement

No database schema changes are required.

## Cup and cup stage

- A cup has an ID, name, type, supported groups, an event count, and an active state.
- A cup stage belongs to a cup and an event. Disabling it removes it from active stage queries and invalidates cup tables.
- Existing disable use cases record actor identity and domain events.

## Cup table

- `CupTable` contains ordered stages and ranked `CupTableRow` entries.
- Each row contains place, person identity and name, birth year, club, stage cells, total points, and average points.
- `CupTableStageCell` contains stage ID, points, counted flag, distance ID, and protocol-line ID.
- The cached builder keys the table by cup and group; cup mutations invalidate the cup cache tag.
- Export traverses the complete table, omitting pagination and search filters.

## Distance

- A distance belongs to one event and one group and has length and points.
- Equal distances share event, length, and points and exclude the starting distance.
- Cup group selection can include equal distances. The result set must be unique.
- Event cleanup deletes distances for the event before replacing protocol lines.

## State transitions

- Active cup to disabled cup: editor command, list exclusion, cache invalidation.
- Active stage to disabled stage: editor command, cup table recalculation.
- Cached cup table to invalidated table: explicit clear or cup/stage mutation; next read rebuilds it.
