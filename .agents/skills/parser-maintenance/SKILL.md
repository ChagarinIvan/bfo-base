---
name: parser-maintenance
description: Upgrade and repair protocol parsers in BFO Base when a real fixture exposes a format variant or missing field, using a regression-first TDD workflow.
---

# Parser maintenance

Use this skill for importing/parsing bugs involving protocol fixtures. Keep the change local to
the responsible parser and preserve existing parser behavior.

## Workflow

1. Read `CLAUDE.md`, the constitution, and the active spec. Inspect fixture encoding, title,
   headers, and the problematic row. Locate parser `check()` methods, `ParserFactory`, and
   existing parser tests; identify the parser through the real factory path.
2. Add the real fixture to the existing parser test without replacing old cases. Assert the
   complete public row shape: identity, club, year, rank, runner number, result/time, place,
   completed rank, points, and relevant flags.
3. Run only that test first and record the red failure. Explain which token/cursor or
   normalization rule causes the mismatch before changing production code.
4. Implement the smallest parser fix for the demonstrated variant. Do not weaken `check()`,
   discard malformed rows, or change ordinary-row semantics merely to satisfy a test.
5. Run the new case plus every pre-existing test for that parser, then the broader parser suite
   and appropriate formatting/static checks. Keep fixtures and old expected results intact.
6. Document the parser, input variant, red/green evidence, and remaining ambiguity in the spec
   or review summary.

## BFO-specific guidance

- Parsers live under `app/Models/Parser`; tests live under `tests/Models/Parser` and normally
  extend `AbstractParser`.
- Test public `parse()` and `check()` behavior, not private token helpers.
- In right-to-left parsers, values meaning “no result” must consume exactly their represented
  columns, otherwise year/number/place fields shift.
- Keep domain/application changes out of parser-only fixes; confirm the protocol contract before
  introducing new semantic values.
