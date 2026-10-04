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

## Decision: transaction per stage

**Decision**: Parsing, identification and rank rebuilding each run in one database transaction with the Event row locked. The Event checks stage and generation token before calling the domain worker. Identification loads the remaining lines and saves them after the loop. The rank updater loads affected person IDs and saves each Person inside the same transaction. Event catches errors during a stage and records failure; `EventProcessingFailed` starts cleanup after commit.

**Rationale**: The user chose the existing transaction model. It prevents concurrent deliveries from changing the same Event and makes each stage atomic. A worker crash loses the current stage's progress; the only new run starts after protocol replacement. This trades lock duration and memory for simpler consistency.

## Decision: error contract

**Decision**: An unrecognized file format raises `EventParsingError`. Event catches domain and unexpected parser, repository and calculator exceptions, records a safe message and emits `EventProcessingFailed`. Parsing saves lines inside `Event::parse()` so a persistence failure follows the same path. There is no separate failure method or handler.

**Rationale**: Technical exception text must not reach the UI. Cleanup removes results from a failed stage after commit. A worker killed before the domain catch cannot record an error by this path; that case remains open.

## Decision: rank work and public status

**Decision**: One rank stage iterates people in the current protocol and marks Event `ready` only after all updates commit. Authenticated projections expose status and safe `errorMessage`; guest queries require `ready`. The detail page sends at most one status request at a time and ignores responses after unmount.

## Operational bounds

Default Horizon workers have a 300 second timeout and 128 MiB memory limit; Redis `retry_after` is 360 seconds. The largest available fixture has 130 lines, but that is not a proven maximum for production protocols. Measure longer files before raising these limits. The domain's current parser adapter still imports legacy `ParserFactory`; the user explicitly accepted that dependency for this change.

## Existing call sites and migration inventory (T001)

- Creation and replacement: `AddEventService` and `UpdateEventService` record `EventCreated` and `EventProtocolUpdated`; `AddEventProtocolHandler` and `UpdateEventProtocolHandler` call `AddEventProtocolService`, which creates a run through `EventProtocolProcessor`. `EventProvider` binds the run repository/factory and registers the queued handlers. `CreateEventAction` also depends on the current response path.
- Parsing and identification: `ParseEventProtocolHandler` receives `EventProtocolCreated` and invokes `ParseEventProtocolService`; `IdentifyEventProtocolHandler` receives `EventProtocolParsed` and invokes `IdentifyEventProtocolService`. `ProtocolLinePersonSetHandler` and `RecordEventProtocolLineIdentificationService` still update run progress from line events. These handlers and services must switch to Event id/token or be removed after their consumers move.
- Rank work: `StartEventProtocolRankRebuildHandler` and `StartEventProtocolRankRebuildService` dispatch `RebuildPersonRanksJob`, which fans out to `RebuildPersonRankJob`; `CompleteEventProtocolRankJobService` counts completions. The new single Event rank stage replaces this chain. Existing rank jobs may still serve other callers and require a usage check before deletion.
- CLI and legacy identification: `Console/Kernel.php` schedules `SimpleIndentCommand`, `StartBigIdentCommand`, and `IdentProtocolLineCommand`; the latter two use `IdentLine`. `ProtocolLineIdentService` also enqueues `IdentLine` and dispatches `RebuildPersonRanksJob`. RankCheck, repeat-master backfill, OrientBy sync, and their tests use parts of this service, so those callers need a separate migration or a retained compatibility path.
- Read models and tests: `EventAssembler`, event/distance/protocol-line services and API request tests currently project or filter through `activeProtocol`; `EventProtocolTest`, `ProcessEventProtocolTest`, `HistoricalEventProtocolBackfillTest`, and handler/service tests assert run IDs and the old `failed` state. Replace their assertions with Event status/token and guest-ready visibility.
- Schema removal list: replace the unpublished `event_protocols`, `events.active_event_protocol_id`, and `protocol_lines.event_protocol_id` migrations before application; remove their model, repository, factory, provider bindings, and run-specific events only after all call sites have switched. The two later draft claim/source-path migrations also refer to the removed table and must be deleted or made obsolete before final migration validation.
