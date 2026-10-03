# Event processing status contract

Revised 2026-10-02: authenticated event list and detail reads return active Events in every processing state, including transitional and error states. Guest reads return only active Events whose `processingStatus` is `ready`, selected through `EventResources::readyOnly`. Event detail returns not found for an Event hidden by these filters. Authenticated responses include `processingStatus` and nullable safe `errorMessage`; public responses do not expose `errorMessage`.

Distance lists and ordinary protocol-line reads always apply the Event readiness filter in their repositories, for both authenticated and guest clients. A distance list for a missing, inactive or unready Event returns an empty list. ProtocolLine visibility also retains the active Competition condition.

Internal locking repository queries retain access to active Events and their results while processing. Creating an Event with a protocol returns `parsing`; the internal processing token and per-line progress are not exposed. Authenticated list/detail endpoints support polling every five seconds while processing is transitional. Polling stops on `ready`, an error state or unmount.
