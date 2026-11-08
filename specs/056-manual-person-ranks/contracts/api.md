# API and SPA contract

POST /api/v1/persons/{personId}/manual-ranks, authenticated, body:
{"rankId":6,"achievedOn":"2026-01-01","activatedOn":"2026-01-15"}.
201 returns {id,personId,rankId,achievedOn,activatedOn,created,updated} through assembler.
DELETE /api/v1/persons/{personId}/manual-ranks/{manualRankId}, authenticated, 204.
404 missing/inactive Person or source belonging to another Person; 422 invalid dates/rank,
409 duplicate active tuple. DELETE is disable, no edit route. Rebuild failure rolls everything back.

GET existing rank-histories adds source protocol/manual and manualRankId string|null.
protocolLineId/distanceId/eventId/competitionId string|null; protocol responses unchanged
except new fields. Guests retain public history access, mutations require existing auth middleware.
No list source endpoint: every valid manual source appears in projected history, including juniors.

SPA add dialog rank+two required dates; only real ranks, show validation errors and preserve inputs.
Manual source label, delete confirmation via shared ConfirmDeleteDialog, no protocol-only actions
or fake links. Filter null IDs and skip empty events/cup calls. On successful add/delete refresh
history and parent card; stale route completions do not update another Person.
