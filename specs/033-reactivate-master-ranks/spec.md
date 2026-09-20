# Feature Specification: Restore repeat master-rank activation

**Feature Branch**: `033-reactivate-master-ranks`  
**Created**: 2026-09-20  
**Status**: Draft  
**Input**: User description: "Automatically fill activation for a repeated KMS or MS achievement when the athlete has previously held that activated rank, for both database and console identification flows."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Automatically confirm a repeated KMS or MS (Priority: P1)

An operator imports a protocol and identifies an athlete who has previously held an
activated KMS or MS. When the athlete again fulfils that same rank, the new protocol
line is marked as activated without a manual follow-up action.

**Why this priority**: The athlete's current rank period must remain valid and the
operator must not repeatedly confirm a rank that the athlete has already held.

**Independent Test**: Import or identify a protocol line for an athlete with a prior
activated KMS or MS and verify that its activation date is populated with the event date.

**Acceptance Scenarios**:

1. **Given** an athlete has a historical, activated MS, **When** an identified protocol
   line records a new MS achievement, **Then** that line receives the event date as its
   activation date.
2. **Given** an athlete has a historical, activated KMS, **When** an identified protocol
   line records a new KMS achievement, **Then** that line receives the event date as its
   activation date.
3. **Given** an athlete has never had an activated KMS or MS, **When** the athlete first
   achieves that rank, **Then** the line remains unactivated for manual confirmation.

---

### User Story 2 - Apply the rule in every identification workflow (Priority: P1)

An operator uses either the regular identification process or the console command to
identify protocol lines. The same athlete and protocol data produce the same activation
result in both workflows.

**Why this priority**: Different operational entry points must not create contradictory
rank histories.

**Independent Test**: Run each workflow against equivalent pending protocol lines and
compare their activation dates.

**Acceptance Scenarios**:

1. **Given** a qualifying athlete and an unassigned line, **When** the regular database
   identification workflow assigns the athlete, **Then** the repeated-rank line is activated.
2. **Given** equivalent qualifying data, **When** the console identification workflow
   assigns the athlete, **Then** the repeated-rank line is activated.

---

### User Story 3 - Preserve rank calculation semantics (Priority: P2)

After repeated-rank activation, rebuilding the athlete's rank history uses the activated
line and does not require manual data correction. Activation is a required part of
identification and happens before any rebuild is started.

**Why this priority**: Activation is valuable only if downstream rank periods use it.

**Independent Test**: Rebuild rank history after automatic activation and verify that the
new rank period starts on the event date.

**Acceptance Scenarios**:

1. **Given** a repeated KMS or MS is identified, **When** identification completes,
   **Then** its activation date is saved before rank recalculation begins.
2. **Given** a repeated KMS or MS was automatically activated, **When** the athlete's
   ranks are recalculated, **Then** the calculated history contains the new activated period.

### Edge Cases

- A historical achievement of the same rank without an activation date does not qualify
  the athlete for automatic activation.
- A prior lower or higher rank does not qualify a repeated KMS or MS for automatic activation.
- Re-identifying an already assigned line does not overwrite an existing activation date.
- Unidentified protocol lines remain unchanged until an identification workflow assigns an athlete.
- Historical correction skips non-target ranks, first-time achievements, unactivated-prior
  achievements and lines with an existing activation date.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST determine whether an athlete previously held an activated
  KMS or MS using the athlete's complete recorded rank history.
- **FR-002**: When an identification workflow assigns an athlete to a new KMS or MS
  protocol line and FR-001 is true for that same rank, the system MUST set the line's
  activation date to the event date.
- **FR-003**: The regular database identification workflow and the console identification
  workflow MUST apply identical repeated-rank activation rules.
- **FR-004**: A first KMS or MS achievement, or a previous unactivated achievement, MUST
  remain unactivated for manual confirmation.
- **FR-005**: The system MUST preserve an existing protocol-line activation date and MUST
  NOT replace it during identification.
- **FR-006**: Identification MUST save the resulting activation date before it requests
  rank recalculation; recalculation MUST only consume the saved activation state and MUST
  NOT infer or amend it.
- **FR-007**: The feature MUST retain existing automatic activation behaviour for ranks
  other than KMS and MS.
- **FR-008**: The system MUST provide an administrator-run historical correction that
  applies the same KMS/MS repeat-activation rule without using a database migration.
- **FR-009**: The historical correction MUST support a non-mutating preview mode that
  reports affected protocol-line and athlete counts.
- **FR-010**: In apply mode, the historical correction MUST save all eligible activation
  dates before it rebuilds each affected athlete's ranks.

### Key Entities

- **Protocol line**: An athlete result that records the achieved rank and, when confirmed,
  its activation date.
- **Rank history**: The athlete's accumulated qualified achievements and their activation
  states.
- **Identification workflow**: A process that associates a protocol line with an athlete.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In automated coverage, 100% of repeated KMS and MS identification cases
  with a prior activated same-rank record receive the event-date activation.
- **SC-002**: In automated coverage, 100% of first-time and historically unactivated KMS
  and MS cases remain pending manual activation.
- **SC-003**: Equivalent regular and console identification scenarios produce identical
  protocol-line activation dates.
- **SC-004**: Rank recalculation begins only after the automatically activated repeated
  achievement is saved, and its resulting history includes that achievement without manual intervention.
- **SC-005**: Preview mode changes zero protocol lines; apply mode corrects every eligible
  line in automated coverage and rebuilds no unaffected athlete.

## Assumptions

- The recorded event date is the correct activation date for a repeated KMS or MS.
- A previous rank is considered held only when its historical protocol line has a
  non-empty activation date.
- The feature covers KMS and MS only; MSМК keeps its existing manual-confirmation rule.
- Existing protocol lines retain any operator-provided activation date.
- Historical correction is run explicitly by an administrator with a supplied user ID;
  it is not part of deployment migrations.
