# Data Model

| Entity | Required fields |
|---|---|
| Result group | normalized group name, source order; М1–М5 and Ж1–Ж5 |
| Protocol line | surname, first name, club, birth year, rank, runner number, time, place, completed rank, group |
| Incomplete result | `п.п.<rule>` source marker mapped to null time/place and retained identity |
| Fixture signature | Albatros-Timing title, Windows-1251 encoding, `h2` group, `pre` rows |

The parser produces the existing protocol-line array contract; it introduces no
new persistence model or domain value.
