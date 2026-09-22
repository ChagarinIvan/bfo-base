# План реализации: импорт Word HTML протоколов Albatros-Timing

**Ветка**: `034-albatros-word-html` | **Дата**: 2026-09-22 | **Спека**: [spec.md](spec.md)

## Резюме

Добавить regression-кейс для production fixture `20260404.htm` и поддержать Word
HTML-вариант Albatros-Timing. Parser должен нормализовать Windows-1251 до DOM-разбора,
собрать отдельные визуальные строки Word в логический блок группы и передать его
существующим правилам извлечения полей. Это сохраняет обычные результаты и статусы
без результата во всех десяти группах.

## Technical Context

**Language/Version**: PHP 8.5, Laravel 13.

**Primary Dependencies**: DOMDocument, DOMXPath, mbstring, PHPUnit 13.

**Storage**: production fixture в `storage/tests`; постоянная схема не меняется.

**Testing**: `tests/Models/Parser/AlbatrosTimingParserTest.php` через public parser
factory.

**Target Platform**: Linux PHP worker и HTTP runtime.

**Project Type**: Laravel web application; изменение во внутреннем import parser.

**Performance Goals**: fixture из 248 строк десяти групп разбирается одним линейным
проходом по HTML-блокам без базы данных и без дополнительных проходов по результатам.

**Constraints**: минимальная правка только Albatros-Timing parser-а; сохраняются
прежние fixtures, parser selection и public row contract.

**Scale/Scope**: один production-вариант Word HTML, 10 групп и 248 строк; без
миграций, API и SPA.

## Constitution Check

*GATE: Passed before Phase 0 research; re-check after Phase 1 design.*

| Gate | Status | Evidence |
|------|--------|----------|
| Слоистость | Pass | Изменяется существующий parser и parser test; новые legacy-сервисы не создаются. |
| Тесты | Pass | Реальный fixture добавляется в существующий public parser contract test. |
| Нормализация | Pass | Кодировка нормализуется один раз перед DOM-разбором, до извлечения групп и полей. |
| N+1 / неограниченные выборки | Pass | БД и query paths не затрагиваются; обработка документа линейна. |

## Project Structure

### Documentation (this feature)

```text
specs/034-albatros-word-html/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/albatros-timing-parser.md
└── tasks.md
```

### Source Code (repository root)

```text
app/Models/Parser/
└── AlbatrosTimingParser.php          # document normalization and row extraction

storage/tests/2026/
└── 20260404.htm                      # production Word HTML regression fixture

tests/Models/Parser/
└── AlbatrosTimingParserTest.php      # public parser contract examples
```

**Structure Decision**: parser-only change in the existing Albatros-Timing parser and
its fixture-driven test suite.
