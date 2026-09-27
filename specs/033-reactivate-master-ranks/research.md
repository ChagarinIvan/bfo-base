# Research: Restore repeat master-rank activation

## Decision: Keep the activation rule in the legacy identification service

**Rationale**: Both required paths already use `ProtocolLineIdentService`: fast
identification invokes `identPersons()`, while the queue command receives the service as
a dependency. The surrounding parsing/identification workflow remains legacy by design
for this feature. A common helper there prevents drift without a broader migration.

**Alternatives considered**:

- A new Domain/Application service: rejected because it would require extracting all
  parsing/identification entry points and exceeds this bug-fix scope.
- A rule in rank rebuild: rejected because activation must be saved before rebuild and a
  rebuild must only consume activation state.

## Decision: Read activated same-rank protocol lines with a set-based existence query

**Rationale**: The source of activation is `protocol_lines.activate_rank`. An existence
query correlates each candidate line with prior lines by athlete, canonical rank,
non-null activation and an earlier event date. It answers the rule without trusting a
potentially stale derived rank-history projection or issuing one query per candidate.

**Alternatives considered**:

- Read `person_rank_histories`: rejected because it is a derived projection replaced on
  every rebuild, while the protocol line is the authoritative activation record.
- Load every previous line into PHP: rejected because the database existence query is
  bounded and avoids an unbounded history load.
- Run one existence query per athlete/rank: rejected because a production identification
  batch would create N+1 database queries.

## Decision: Limit automatic repeat activation to KMS and MS

**Rationale**: The requested issue explicitly covers KMS and MS. Existing automatic
activation for ordinary ranks remains untouched; MSМК remains manual until separately
specified.

## Decision: Preserve explicit activation values

**Rationale**: The helper only considers lines whose `activate_rank` is null. This makes
the operation safe to repeat and avoids overriding an operator's date.
