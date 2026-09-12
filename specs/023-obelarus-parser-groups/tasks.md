# Задачи: корректный импорт групп OBelarus.net

**Input**: Артефакты из `specs/023-obelarus-parser-groups/`

## Phase 1: Подготовка

- [X] T001 Проверить фактическую кодировку, заголовки групп и строки fixture в `storage/tests/2026/20260911.htm`.

## Phase 2: User Story 1 — корректные группы (P1) 🎯 MVP

**Цель**: production-протокол возвращает строки с группами, включая обычные и снятые результаты.

**Независимый тест**: `vendor/bin/phpunit tests/Models/Parser/OBelarusNetParserTest.php` с новой строкой data provider.

- [X] T002 [US1] Добавить в `tests/Models/Parser/OBelarusNetParserTest.php` regression-кейс `2026/20260911.htm` для обычного М21, двух обычных участников иных групп и снятого участника без времени и места.
- [X] T003 [US1] Запустить `OBelarusNetParserTest` в red-состоянии и зафиксировать отсутствие строк fixture.
- [X] T004 [US1] Нормализовать кодировку входного документа перед DOM-разбором в `app/Models/Parser/OBelarusNetParser.php`, не меняя parser check и правила обычных строк.
- [X] T005 [US1] Запустить новый и все ранее существующие случаи `tests/Models/Parser/OBelarusNetParserTest.php` в green-состоянии.

## Phase 3: User Story 2 — совместимость OBelarus.net (P2)

**Цель**: прежние OBelarus.net-варианты не регрессируют.

**Независимый тест**: полный набор parser-тестов проходит с неизменёнными прежними ожиданиями.

- [ ] T006 [US2] Запустить `vendor/bin/phpunit tests/Models/Parser` и устранить только регрессии, связанные с кодировкой OBelarus.net в `app/Models/Parser/OBelarusNetParser.php`.

## Phase 4: Полировка и гейты

- [ ] T007 Выполнить проверки из `specs/023-obelarus-parser-groups/quickstart.md`: `composer stan`, `composer cs` и `git diff --check`.
- [ ] T008 Сверить `spec.md`, `plan.md`, `tasks.md` и отметить выполненные задачи в `specs/023-obelarus-parser-groups/tasks.md`.

## Зависимости и порядок

- T001 → T002 → T003 → T004 → T005 → T006 → T007 → T008.
- T002 — обязательный red regression test до T004.
- MVP — завершение T005; T006 подтверждает обратную совместимость.

## Параллелизм

Для этого узкого parser bugfix параллельных задач нет: выбор ожидаемых строк, red
проверка и минимальная правка зависят друг от друга.
