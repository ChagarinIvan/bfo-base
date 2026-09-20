# Data Model: Cup Event Context

`CupEventContextDto` is a read-only projection:

| Field | Source | Purpose |
|---|---|---|
| eventId | CupEvent event | group contexts by displayed event |
| cupEventId | CupEvent id | legacy stage destination |
| cupId | Cup id | destination and identity |
| cupName | Cup name | popover label |
| cupType | Cup type | established icon |
| href | derived from cup + first group | existing legacy stage view |

No schema change is needed.
