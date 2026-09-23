# Feature Specification: Albatros 20260919 parser

**Feature Branch**: `035-albatros-20260919-parser`  
**Created**: 2026-09-23  
**Status**: Draft

**Input**: Parse `storage/tests/2026/20260919.htm`, a Windows-1251 Word HTML
protocol exported by Albatros-Timing.

## Context

The file is recognized by `AlbatrosTimingParser`, but two details of this
export are different from older fixtures: incomplete rows use a value such as
`п.п.20.10` and the final participant row of one group is followed by the
separator and footer in the same `<pre>` node. The old right-to-left cursor
then shifts the runner number and can emit footer text as a participant.

## User Scenarios & Testing

### User Story 1 — Import the result protocol (P1)

An administrator uploads the 2026-09-19 protocol and receives groups and
participant results without manually editing the HTML.

**Acceptance scenarios**:

1. Given the supplied fixture, when it is passed to the parser factory, then
   a compatible parser is selected.
2. Given a selected parser, when it reads the fixture, then it returns every
   valid group and result row in the source order.
3. Given an incomplete row with `п.п.<rule>`, when it is parsed, then the
   participant identity and runner number are retained while result and place
   remain empty.
4. Given a separator joined to a participant row, when it is parsed, then the
   participant is imported and the following footer is ignored.

### Edge cases

- Source encoding must not corrupt Cyrillic names, clubs, or group labels.
- Empty, disqualified, non-started and incomplete rows retain established
  parser semantics.
- Existing Albatros fixtures remain parseable by their current parser.
- A footer line such as `Класс дистанции` or `Уровень соревнований` is never a
  protocol participant.

## Functional Requirements

- **FR-001**: ParserFactory MUST recognise the supplied fixture.
- **FR-002**: The selected parser MUST return normalized groups and protocol
  lines using the existing parser output contract.
- **FR-003**: Cyrillic source data MUST be decoded before extraction.
- **FR-004**: A regression fixture test MUST assert parser selection and key
  rows for `2026/20260919.htm`.
- **FR-005**: Existing parser fixtures MUST retain their current results.
- **FR-006**: The parser MUST normalize every `п.п.<rule>` marker to the
  established no-result semantics without changing identity columns.
- **FR-007**: A run of separator dashes embedded in a `<pre>` node MUST end
  the participant section before footer text is considered.
- **FR-008**: The fixture regression MUST cover an ordinary row, an incomplete
  row and the last row before the embedded separator.

## Success Criteria

- The supplied fixture parses through the public parser flow without an error.
- Regression tests prove the extracted groups, names, clubs, years, places and
  times for representative rows.
- Existing Albatros parser tests remain green.
- The fixture yields 273 protocol lines across groups М1–М5 and Ж1–Ж5, with no
  line having a zero runner number.

## Scope

Includes parser detection, decoding and extraction needed for this fixture.
Excludes changes to imported event scoring, persistence and SPA presentation.
