# План реализации: корректный импорт групп OBelarus.net

**Ветка**: `023-obelarus-parser-groups` | **Дата**: 2026-09-13 | **Спека**: [spec.md](spec.md)

## Резюме

Добавить regression-кейс для production fixture `20260911.htm` и локально
нормализовать Windows-1251 OBelarus.net HTML до разбора DOM. Это сохраняет текст заголовка
каждой группы, поэтому обычные и снятые участники получают правильную группу.

## Summary

[Extract from feature spec: primary requirement + technical approach from research]

## Technical Context

<!--
  ACTION REQUIRED: Replace the content in this section with the technical details
  for the project. The structure here is presented in advisory capacity to guide
  the iteration process.
-->

**Language/Version**: PHP 8.5, Laravel 13.

**Primary Dependencies**: DOMDocument, DOMXPath, mbstring, PHPUnit 13.

**Storage**: production fixture в `storage/tests`; постоянная схема не меняется.

**Testing**: `tests/Models/Parser/OBelarusNetParserTest.php` через public parser factory.

**Target Platform**: Linux PHP worker и HTTP runtime.

**Project Type**: Laravel web application; изменение во внутреннем import parser.

**Performance Goals**: один fixture разбирается в существующих границах parser-тестов;
дополнительных проходов по строкам не добавлять.

**Constraints**: минимальная правка только OBelarus.net parser-а; сохраняются прежние fixtures,
check() и public row contract.

**Scale/Scope**: один production-вариант OBelarus.net HTML, 33 блока групп и 531 строка
результатов; без миграций, API и SPA.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Evidence |
|------|--------|----------|
| Слоистость | Pass | Изменяется существующий parser и parser test; новые legacy-сервисы не создаются. |
| Тесты | Pass | Реальный fixture добавляется в существующий public parser contract test. |
| Нормализация | Pass | Кодировка нормализуется один раз перед DOM-разбором, до сравнения и извлечения групп. |
| N+1 / неограниченные выборки | Pass | БД и query paths не затрагиваются. |

## Project Structure

### Documentation (this feature)

```text
specs/023-obelarus-parser-groups/
├── plan.md              # This file ($speckit-plan command output)
├── research.md          # Phase 0 output ($speckit-plan command)
├── data-model.md        # Phase 1 output ($speckit-plan command)
├── quickstart.md        # Phase 1 output ($speckit-plan command)
├── contracts/           # Phase 1 output ($speckit-plan command)
└── tasks.md             # Phase 2 output ($speckit-tasks command - NOT created by $speckit-plan)
```

### Source Code (repository root)
<!--
  ACTION REQUIRED: Replace the placeholder tree below with the concrete layout
  for this feature. Delete unused options and expand the chosen structure with
  real paths (e.g., apps/admin, packages/something). The delivered plan must
  not include Option labels.
-->

```text
app/Models/Parser/
└── OBelarusNetParser.php          # HTML decoding and public row extraction

storage/tests/2026/
└── 20260911.htm                  # production regression fixture

tests/Models/Parser/
└── OBelarusNetParserTest.php      # public parser contract examples
```

**Structure Decision**: parser-only change in the existing OBelarus.net parser and its
fixture-driven test suite.

| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
