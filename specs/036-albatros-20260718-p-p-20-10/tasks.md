# Задачи: `п.п. 20.10` в Albatros-Timing

## Phase 1: Подготовка

- [X] T001 Проверить кодировку, DOM-структуру, группы, количество строк и
  фактическое написание маркера в `20260718.htm`.

## Phase 2: Regression и исправление

- [X] T002 Добавить fixture в `AlbatrosTimingParserTest` с обычной строкой и
  четырьмя участниками с `пп. 20.10` из Ж, М и Open-групп.
- [X] T003 Зафиксировать defect: старый parser создаёт время `20:10:00` и
  runner number `0`.
- [X] T004 Нормализовать варианты `пп.`/`п.п.` и `.`/`,` перед правым cursor,
  сохранив null time/place и identity fields.
- [X] T005 Не менять ParserFactory и прежние parser semantics.

## Phase 3: Проверки и артефакты

- [X] T006 Прогнать focused regression и весь `tests/Models/Parser`.
- [X] T007 Прогнать `composer cs`, `composer stan` и `git diff --check`.
- [X] T008 Сверить spec, plan, research, data-model, contract, checklist и
  quickstart с фактическим изменением.

## Checkpoints

- Red: `пп. 20.10` разбирался как Carbon `20:10:00`, номер становился `0`.
- Green: fixture возвращает 237 строк; ни одна строка не имеет нулевого номера
  из-за этого маркера, а четыре заявленных кейса имеют `time/place = null`.

## Порядок

T001 → T002 → T003 → T004 → T005 → T006 → T007 → T008.
