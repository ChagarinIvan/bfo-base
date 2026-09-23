# Research: Albatros 20260919 parser

## Known facts

- Fixture: `storage/tests/2026/20260919.htm`.
- Existing Albatros parsers include timing, relay, relay-with-headers and
  timing-with-region variants.
- Historical fixtures show mojibake when read with an incorrect encoding;
  decoding must be determined from source bytes, not visible terminal output.

## Findings

- The raw file is valid Windows-1251 (`mb_check_encoding(..., 'UTF-8')` is
  false) and declares `charset=windows-1251`.
- The title contains `Albatros-Timing`; `ParserFactory` therefore already
  selects `AlbatrosTimingParser`.
- There are ten groups: М1–М5 and Ж1–Ж5. The public parse result contains 273
  lines after excluding the footer text accidentally consumed by the old
  cursor.
- Incomplete rows contain `п.п.24.4` or `п.п.20.10` followed by dash columns.
  The existing parser only recognized the shorter token `пп`.
- At least one `<pre>` contains a participant row immediately followed by the
  long separator and footer, without a line break.

## Decisions

Keep the existing parser and normalize these two source quirks before the
existing right-to-left extraction. A new parser or factory rule would duplicate
the already compatible Albatros contract.
