# Paginated Listing Matrix

| Listing family | Repository | Main filters/joins | Default ordering | Relation/aggregate risk |
|---|---|---|---|---|
| Persons | `EloquentPersonRepository` | active, ids, club, rank, name | lastname, firstname, id | club join; active club |
| Events | `EloquentEventRepository` | competition, ids, group, competition name, year, date, cup relation | date, id or requested sort | distance/cup joins; distinct; protocol count |
| Groups | `EloquentGroupRepository` | active, name, exclude id | distances count desc, id | aggregate count |
| Competitions | `EloquentCompetitionRepository` | year, name, date | from desc | date-range search |
| Clubs | `EloquentClubRepository` | active, name | name, id | active-person count |
| Person payments | `EloquentPersonPaymentRepository` | active person, person, year | payment id desc | person join |
| Person prompts | `EloquentPromptPaymentRepository` | prompt/person criteria | repository default | active person/prompt relations |
| Protocol lines | `EloquentProtocolLinesRepository` | event/distance/person/name criteria | repository default | club/person/distance resources |
| Rank checks | `EloquentRankCheckRepository` | rank-check state | repository default | run metadata |
| Rank-check rows | `EloquentRankCheckRowRepository` | rank-check id/status/search | position/id | person relation and row order |

This inventory is the starting point for implementation. Exact criteria and index decisions are completed against runtime query plans in `query-profiles.md`.

## Implementation audit

- Every repository listed above constructs `Slice` with `EloquentQueryAdapter`; no listing path retains a Pagerfanta count adapter.
- Root selections are preserved for join-based queries (`events.*`, `groups.*`, `protocol_lines.*`, `persons_payments.*`, `rank_check_rows.*`), and event filtering uses `distinct` where a distance/cup join can duplicate an event.
- Paginated orderings now end with a stable root identifier: person, event, group, club, payment, prompt, protocol line, rank check and rank-check row paths already had one; competition and custom-sorted event/rank-row paths were completed during this feature.
- Existing supporting indexes were reviewed: foreign-key indexes cover relation joins, `person(club_id, active)` and `person(current_rank, active)` cover the most selective person filters, and rank-check rows have `(rank_check_id, position)`. New composite indexes remain evidence-gated because the test database was unavailable for explain plans.
