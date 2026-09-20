# Identification flow contract

## Inputs

- An identified protocol line with an athlete, completed rank and event date.
- A workflow impression identifying the actor and time of identification.

## Repeat KMS/MS activation result

| Condition | `activate_rank` after identification |
|---|---|
| KMS/MS with prior activated same-rank line | Current line's event date |
| KMS/MS without prior activated same-rank line | Unchanged (`null`) |
| Existing non-null activation | Unchanged |
| Any other rank | Existing behaviour unchanged |

## Ordering invariant

Both fast database identification and queue/console identification complete this result
before they dispatch or invoke rank rebuilding for the athlete.
