# Parser Fixture Contract

Input is `storage/tests/2026/20260919.htm`. `ParserFactory` selects
`AlbatrosTimingParser` from the existing title marker and returns the established
iterable of protocol-line arrays.

For each valid source row, the parser preserves group, surname, first name,
club, year, rank and runner number. A completed row has time/place and may have
completed rank. A `п.п.<rule>` row has null time/place and keeps its identity
columns. Separator and footer text are not returned as rows.
