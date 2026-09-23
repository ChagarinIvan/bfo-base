# План реализации: `п.п. 20.10` в Albatros-Timing

**Ветка**: `036-albatros-20260718-p-p-20-10` | **Спека**: [spec.md](spec.md)

## Резюме

Добавить реальный fixture `20260718.htm` в существующий public parser contract и
расширить нормализацию маркера результата без новой ветки ParserFactory. Текст
`пп. 20.10` должен превращаться во внутренний маркер отсутствия результата до
правого разбора колонок.

## Technical Context

**Стек**: PHP 8.5, Laravel 13, DOMDocument/DOMXPath, mbstring, PHPUnit 13.

**Изменяемые места**: `AlbatrosTimingParser`, `AlbatrosTimingParserTest` и
артефакты спецификации. Миграции и application services не нужны.

**Формат**: Windows-1251 Word HTML, 35 групп и 237 строк.

**Проверки**: focused parser test, весь `tests/Models/Parser`, `composer cs`,
`composer stan`, `git diff --check`.

## Constitution Check

| Gate | Status | Evidence |
|---|---|---|
| Слоистость | Pass | Меняется существующий parser, новый legacy-сервис не создаётся. |
| Регрессионный тест | Pass | Используется реальный fixture и public factory flow. |
| Нормализация | Pass | Вариант маркера нормализуется перед извлечением правых колонок. |
| Производительность | Pass | Один линейный проход по уже загруженному HTML, БД не затрагивается. |

## Дизайн

1. Определить фактическое написание маркера в байтах и DOM-тексте.
2. Добавить red regression cases с завершённым участником и несколькими `пп.
   20.10`.
3. Распознавать точки, пробелы и `.`/`,` в номере правила и переводить их в
   существующий `пп`-маркер.
4. Сохранить правый cursor и public row contract.
5. Проверить старые Albatros fixtures.

## Структура проекта

```text
app/Models/Parser/AlbatrosTimingParser.php
tests/Models/Parser/AlbatrosTimingParserTest.php
storage/tests/2026/20260718.htm
specs/036-albatros-20260718-p-p-20-10/
```

**Решение**: узкий parser-only bugfix, без отдельного parser class и без изменений
factory selection.
