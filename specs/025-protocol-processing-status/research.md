# Research

## Decision: persist processing state on EventProtocol

Queued parsing and later scheduled identification are independent processes, so a stored state is the only reliable public contract. That state belongs to the immutable `EventProtocol` run, not to `Event`: one event may receive a newer protocol while jobs of an older one are still finishing. `Event` stores only the active-run reference. Deriving state from protocol-line counts cannot distinguish parsing delay, identification, rank rebuild and failure.

## Decision: status transitions follow existing jobs

The create/update protocol handlers enter parsing and record failure on their `EventProtocol`. The identification command emits the `ProtocolLine` aggregate event after setting a person. An application handler records that line exactly once in the matching run and creates one rank batch when all lines are identified. Only completion of the stored batch identity can mark that run ready; duplicate or stale messages are ignored.

## Decision: public visibility requires ready

Public read queries filter to ready events; authenticated projections include every state. This prevents unpublished partial results from being exposed.

## Decision: migrate historical results explicitly

The schema migration creates a ready active `EventProtocol` only for historical events with persisted protocol results. Events without a protocol receive no active run and remain non-public. Ambiguous or partial historical data must not be promoted to ready automatically.

## Decision: detail-page polling only

The event detail page refreshes every five seconds during transition states and cancels the timer on terminal state/unmount. Listing shows the state returned by its normal load and does not create background polling per row.
