# Event processing status contract

Authenticated event list and detail responses include the active `EventProtocol` status: `queued`, `parsing`, `identifying`, `rebuildingRanks`, `ready`, or `failed`. Public list/detail responses include only events whose active protocol is `ready`. The SPA polls event detail reads every five seconds solely for the first four statuses.
