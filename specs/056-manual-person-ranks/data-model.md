# Data model

manual_person_ranks: id UUID PK, person_id FK, rank unsigned tinyint, achieved_on date,
activated_on date NOT NULL, active bool, created_at/by, updated_at/by. Index person_id/active.
ManualRankInput validates real rank, date ordering and future achievement against supplied Clock.
Pure aggregate has stable id, source input, created/updated Impression; create records Added,
disable records Disabled. drainEvents clears once after successful persistence.

person_rank_histories: existing four source FK IDs nullable; nullable manual_rank_id UUID FK.
API source derived from manual_rank_id. All calculator copy paths preserve identity.
RankFact adds optional manualRankId (default null preserving protocol constructors).
Manual facts require no protocol references, use explicit activation, bypass age filter.
On rollback migration removes manual projection rows before restoring NOT NULL references,
then drops source table. Existing protocol rows retained.
