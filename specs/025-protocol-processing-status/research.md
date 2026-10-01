# Research

## Decision: Event owns the current protocol lifecycle

**Decision**: Put the processing status and stage transitions on `Event`; do not create a separate `EventProtocol` aggregate or keep a history of processing runs in 025.

**Rationale**: The domain has one current protocol path on an Event, and only the current protocol is exposed or used by downstream services. A second persisted aggregate duplicated “which protocol is current” and forced every read/write path to join through `active_event_protocol_id`. Event owns a generation token because status alone cannot distinguish a delayed task for the old file from a new task in the same stage.

**Alternatives considered**:
- Keep one `EventProtocol` per upload: rejected by the user because its lifecycle is the Event's own lifecycle and it duplicates currentness.
- Infer status from line/rank data: rejected because it cannot distinguish parsing, identification, rank rebuild and failure.

## Decision: adapt, do not change, legacy ParserInterface

**Decision**: Add Domain `ProtocolParser` returning `list<ProtocolLineInput>`. Infrastructure standard adapter selects an existing `ParserInterface` through `ParserFactory`, maps its returned arrays to the DTOs, and keeps the old parser contract untouched.

**Rationale**: The parser ecosystem already contains format-specific behavior; changing its interface would expand the feature and destabilize every format. The adapter gives Application/Domain a typed boundary without moving parsing rules into the legacy parser classes.

**Alternatives considered**:
- Change `ParserInterface` output: rejected because it is a shared legacy contract.
- Pass raw parser arrays through Application and factories: rejected because it leaks undocumented shapes across layers.

## Decision: stage transitions are aggregate methods and events

**Decision**: `Event::parse()` owns `parsing → identifying` and publishes `EventParsed`; `Event::ident()` completes `identifying → rebuildingRanks` after all current lines have a person; `Event::updateRanks()` completes `rebuildingRanks → ready` after all affected people have been recalculated. `Event::failProcessing()` records `parsingError`, `identifyingError`, or `rebuildingRanksError` according to the current stage, together with a safe `errorMessage`.

**Rationale**: Application acquires the external lock and loads Event; Event owns each stage loop and transition. Domain services called from Event use Domain repository ports to save each completed ProtocolLine or Person before the next iteration. New domain code has no direct Eloquent access; adapters remain in Infrastructure. This is the user's chosen exception to the current Application persistence orchestration rule in Constitution VII; the constitution update is deferred to separate work. Parsing saves lines and status in one transaction. Identification and rank work save each result without a stage-wide transaction, so a crash does not erase earlier progress. The next handler starts after Event's successful save.

## Decision: save identification progress per line

**Decision**: Hold one external Event lock through identification and save each identified line before the next line without a stage-wide transaction. Leave Event in `identifying` until an existence check finds no unassigned current lines. Replay skips assigned lines. Do not use `IdentLine` for this pipeline; a separate manual resume command is outside 025.

**Rationale**: A single transaction for all lines would lose all progress on interruption. The line's persisted `person_id` is sufficient progress state and avoids another queue or per-line counter.

## Decision: recalculate ranks in one background stage

**Decision**: Hold the same external Event lock and iterate unique people from the current protocol in one background process. Reuse the existing rank calculator and Person update policy, saving each person before the next. Mark Event `ready` only after the entire pass succeeds; replay recomputes from persisted rank facts.

**Rationale**: This removes per-person rank jobs, batch tracking and completion races. Event still validates its processing token before writes, so a delayed stage cannot finish a replacement protocol.

## Decision: status remains the public contract

**Decision**: Authenticated projections read status and nullable `errorMessage` from Event; guest event, distance and protocol-line queries require `ready` and never expose the message. Creation returns `parsing` with a cleared message because Event itself owns the state.

**Rationale**: API and SPA behavior stays stable except removing the obsolete queued/null-wait phase introduced only to wait for EventProtocol creation.

The draft `EventProtocol` migrations have not been applied; there are no historical `failed` runs to translate. The replacement Event schema writes only the three stage-specific error statuses. Existing completed protocol results still need the normal `ready` backfill.

## Operational risk to validate in plan review

The external lock is an application-level port backed by a Redis adapter for replacement, identification and ranks. Parsing instead runs once in a DB transaction with the Event row locked. A parse failure rolls back partial lines and records `parsingError` immediately under a new row lock transaction. Identification and ranks hold the external lock through their loops without a stage-wide transaction. The lock TTL exceeds the hard worker timeout, `retry_after` exceeds that timeout, and release happens in `finally`; a detected loss of ownership aborts work. Refresh can be used during a long loop. This does not fence a write if expiry occurs between the ownership check and save, so timeout/TTL configuration and monitoring are required and the remaining race is explicitly accepted. Generation tokens and idempotent writes help with delivery, but do not make the check/write pair atomic. Verify query count, worker duration and memory with the largest available fixture. A per-run cache must not be static in a long-lived worker.

Current legacy consumers of `IdentLine` are `ProtocolLineIdentService`, `IdentProtocolLineCommand` and `StartBigIdentCommand`. `Console/Kernel.php` schedules queue identification every 30 seconds, global simple identification daily and big identification daily. These global commands can mutate lines while Event is `identifying`; the schedule must be removed or scoped away from transitional events before the new pipeline is enabled. `BackfillRepeatMasterRankActivationCommand`, `RankCheck` matching and existing tests also use methods of `ProtocolLineIdentService`, so deleting that class requires migrating those consumers first.

## Existing call sites and migration inventory (T001)

- Creation and replacement: `AddEventService` and `UpdateEventService` record `EventCreated` and `EventProtocolUpdated`; `AddEventProtocolHandler` and `UpdateEventProtocolHandler` call `AddEventProtocolService`, which creates a run through `EventProtocolProcessor`. `EventProvider` binds the run repository/factory and registers the queued handlers. `CreateEventAction` also depends on the current response path.
- Parsing and identification: `ParseEventProtocolHandler` receives `EventProtocolCreated` and invokes `ParseEventProtocolService`; `IdentifyEventProtocolHandler` receives `EventProtocolParsed` and invokes `IdentifyEventProtocolService`. `ProtocolLinePersonSetHandler` and `RecordEventProtocolLineIdentificationService` still update run progress from line events. These handlers and services must switch to Event id/token or be removed after their consumers move.
- Rank work: `StartEventProtocolRankRebuildHandler` and `StartEventProtocolRankRebuildService` dispatch `RebuildPersonRanksJob`, which fans out to `RebuildPersonRankJob`; `CompleteEventProtocolRankJobService` counts completions. The new single Event rank stage replaces this chain. Existing rank jobs may still serve other callers and require a usage check before deletion.
- CLI and legacy identification: `Console/Kernel.php` schedules `SimpleIndentCommand`, `StartBigIdentCommand`, and `IdentProtocolLineCommand`; the latter two use `IdentLine`. `ProtocolLineIdentService` also enqueues `IdentLine` and dispatches `RebuildPersonRanksJob`. RankCheck, repeat-master backfill, OrientBy sync, and their tests use parts of this service, so those callers need a separate migration or a retained compatibility path.
- Read models and tests: `EventAssembler`, event/distance/protocol-line services and API request tests currently project or filter through `activeProtocol`; `EventProtocolTest`, `ProcessEventProtocolTest`, `HistoricalEventProtocolBackfillTest`, and handler/service tests assert run IDs and the old `failed` state. Replace their assertions with Event status/token and guest-ready visibility.
- Schema removal list: replace the unpublished `event_protocols`, `events.active_event_protocol_id`, and `protocol_lines.event_protocol_id` migrations before application; remove their model, repository, factory, provider bindings, and run-specific events only after all call sites have switched. The two later draft claim/source-path migrations also refer to the removed table and must be deleted or made obsolete before final migration validation.
