# Implementation Plan: Albatros 20260919 parser

## Summary

Extend the existing `AlbatrosTimingParser` with two local normalization rules.
The factory already selects this parser from the `Albatros-Timing` title, so
factory precedence remains unchanged.

## Technical Context

PHP 8.5, Laravel parser adapters in `app/Models/Parser`, PHPUnit fixture tests
in `tests/Models/Parser`. No database migration, API or SPA change.

The fixture is Windows-1251 and contains ten `h2` groups. Each group is a
sequence of `pre` blocks. The parser already decodes this encoding and reads
rows from right to left.

## Constitution Check

Parser adaptation is a bounded compatibility repair. The fixture is retained,
tests are written first, and no Application/Domain API is changed. **PASS**.

## Design

1. Add the real fixture to `AlbatrosTimingParserTest` and assert public fields.
2. Normalize embedded dash separators while preserving standalone separator
   rows so the existing line offsets remain valid.
3. Normalize `п.п.<rule>` plus trailing dashes to the parser's existing `пп`
   no-result marker before token extraction.
4. Run the focused parser test, all parser tests, and static checks.

## Constitution Check

PASS. The change is limited to the responsible parser and its regression
fixture case. It preserves the existing public protocol-line shape, does not
introduce a repository or persistence model, and keeps old fixtures in the
same test provider.

## Project Structure

- `app/Models/Parser/AlbatrosTimingParser.php`: normalization and extraction.
- `tests/Models/Parser/AlbatrosTimingParserTest.php`: fixture regression.
- `storage/tests/2026/20260919.htm`: real source fixture.
- `specs/035-albatros-20260919-parser/`: specification and delivery records.
