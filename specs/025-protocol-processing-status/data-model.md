# Data Model

## Event

The Event owns one current protocol and its processing lifecycle. A newly created ordinary Event starts in `parsing`; an aggregate Event starts in `ready` with results derived from its source Events. Both status and processing token are mandatory. Replacing the protocol resets processing to `parsing`; 025 does not retain prior uploads as separate runs.

The aggregate Event and its derived distances and lines are saved in one synchronous transaction. Its save does not emit `EventCreated` or start processing. Derived lines retain combined results but have empty `complete_rank` and null `activate_rank`, so they do not award ranks.

| Data | Purpose |
|---|---|
| `processing_status` | Required: `parsing`, `identifying`, `rebuildingRanks`, `ready`, `parsingError`, `identifyingError`, `rebuildingRanksError`. |
| `processing_token` | Identifies the current protocol generation so a delayed stage cannot change a newer protocol with the same status. |
| `error_message` | Nullable safe user-facing error text; set with a stage error status, cleared when a new protocol starts. |
| `file` | Path for the current protocol, already owned by Event. |
| `updated` | Actor and time of the latest processing transition. |

Identification completes after all current protocol lines have a `person_id`; Event needs no per-line ID list or counter. Event catches errors during the stage and records `identifyingError` with `EventProcessingFailed`; cleanup removes derived results after commit. A worker killed before the domain catch can leave Event transitional.

## ProtocolLineInput

Immutable domain input returned by `ProtocolParser`: participant fields, group name and distance length/points. It is produced by adapting an existing `ParserInterface` result and contains no Eloquent model or persistence ID.

## ProtocolLine

Factory maps `ProtocolLineInput` plus Event/distance context into a persistable ProtocolLine. A line belongs to its Event through its distance/event relation; `event_protocol_id` is unnecessary once the separate processing run is removed. `person_id` records successful identification. Existing policy allows replacing the current protocol to delete old derived lines.

## State transitions

```text
create with protocol --> parsing --> identifying --> rebuildingRanks --> ready
aggregate derived results -------------------------------------------> ready
                              |             |                    |
                              v             v                    v
                        parsingError identifyingError   rebuildingRanksError
```

- Parsing accepts only an Event in `parsing`; it stores the parsed lines atomically and emits `EventParsed` as the Event moves to `identifying`.
- Identification starts only from `identifying`; a transaction locks the Event row and commits line assignments together with the transition to `rebuildingRanks`.
- One background rank stage processes unique people from current lines sequentially. Event moves to `ready` only when the full pass succeeds. A repeated pass recomputes ranks from persisted facts.
- All three stage error statuses and `ready` are terminal until a new protocol replaces the current one. A stage failure records its error status; processing resumes only after protocol replacement.
- Old queued events must not move the Event backwards or mutate a later protocol's status. Every stage checks status and token under an Event row lock. Event catches stage errors and records an error for the current token. Forced worker termination before the catch remains an open case.
- On replacement, the cleanup job removes the previous Event distances before parsing. Event records `EventProtocolCleaned` in the cleanup transaction; its queued handler starts the parser after commit. Foreign keys remove protocol lines and rank history rows. Previously linked people are recalculated from surviving results; person and group records remain. A stale token is rejected before cleanup.

## Migration invariants

- The draft `EventProtocol` schema and its `failed` status have not been applied, so no active-run state or rank-batch counters are migrated. Existing Events with complete results are backfilled to `ready`; incomplete historical results become `identifyingError`, and missing results become `parsingError`. Each receives a token before non-null constraints are applied.
- Historical ready Events with persisted results stay ready. Incomplete Events remain guest-hidden through their error status.
- Preserve existing ProtocolLine and Event identities; remove only run ownership columns and obsolete run rows after mapping.
- Public visibility is a projection of `Event.processing_status === ready`, not the presence of a separate run.
