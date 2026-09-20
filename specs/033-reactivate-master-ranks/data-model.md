# Data Model: Restore repeat master-rank activation

## Protocol line

Existing authoritative result record.

| Field | Meaning for this feature | Rule |
|---|---|---|
| `person_id` | Identified athlete | Must be present before evaluation. |
| `complete_rank` | Canonical achieved rank | Only KMS and MS are candidates. |
| `activate_rank` | Confirmation date | Set to event date only when currently null and prior activated same rank exists. |
| `distance_id` → event date | Achievement date | Supplies automatic activation date. |

## Prior activated qualification

No new entity or storage is created. It is an existing different protocol line with the
same `person_id` and canonical `complete_rank`, and a non-null `activate_rank`.

## State transition

```text
identified KMS/MS, activate_rank = null
  ├─ prior activated same rank exists → activate_rank = event date → rebuild may start
  └─ otherwise                     → remains null              → rebuild may start
```

An existing non-null activation is immutable during this transition.
