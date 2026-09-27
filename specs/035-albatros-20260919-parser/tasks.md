# Tasks: Albatros 20260919 parser

- [x] T001 Inspect fixture bytes, encoding, title, group markers, headers and
  malformed row boundaries.
- [x] T002 Add a regression fixture case asserting 273 rows and representative
  public fields.
- [x] T003 Normalize embedded separators and `п.п.<rule>` markers in the
  existing parser without changing factory selection.
- [x] T004 Verify the existing factory selects `AlbatrosTimingParser`; no new
  factory branch is required.
- [x] T005 Run the focused and complete parser suites plus PHP CS and PHPStan.

## Checkpoints

- Red cause: the old cursor treated `п.п.20.10` as a rank/number column and
  consumed footer text when a separator shared a `<pre>` node with a row.
- Green evidence: `AlbatrosTimingParserTest` passes 11 tests and 290 assertions,
  including the new fixture.
