# Задачи: ручная активация I разряда

## Phase 1: Подготовка

- [X] T001 Зафиксировать переходное правило и существующий API в `specs/046-first-rank-activation/spec.md`, `research.md`, `data-model.md`, `contracts/activation.md` и `quickstart.md`.

## Phase 2: Базовая проверка

- [X] T002 Добавить регрессионные сценарии новой и старой активации в `tests/Domain/Rank/RankTest.php`, `tests/Domain/Person/RankCalculatorTest.php` и `tests/Feature/Rank/FirstRankActivationTest.php`.

## Phase 3: User Story 1, новый I разряд

**Goal**: новое выполнение требует ручного подтверждения.  
**Independent Test**: новый и повторный I разряд остаются в истории без даты до вызова существующего API.

- [X] T003 [US1] Исключить I разряд из автоматической активации в `app/Domain/Rank/Rank.php`.
- [X] T004 [US1] Проверить путь создания строки, расчёт и ручную активацию узкими тестами из T002.

## Phase 4: User Story 2, сохранение прежних активаций

**Goal**: ранее активированные I разряды сохраняют дату и срок.  
**Independent Test**: пересчёт сохраняет дату старой активированной строки и не активирует новое выполнение.

- [X] T005 [US2] Проверить существующую запись даты активации при создании строки в `app/Domain/Event/StandardProtocolParser.php` и `app/Domain/ProtocolLine/Factory/StandardProtocolLinesFactory.php`; не добавлять миграцию данных.
- [X] T006 [US2] Проверить неизменность старой даты, отсутствие активации нового выполнения и повторный пересчёт в `tests/Feature/Rank/FirstRankActivationTest.php`.

## Phase 5: Завершение

- [X] T007 Сверить приёмку и контракт в `specs/046-first-rank-activation/spec.md`, выполнить итоговые проверки и отметить задачи в `specs/046-first-rank-activation/tasks.md`.

## Dependencies

T001 → T002 → T003 → T004 → T005 → T006 → T007. Оба пользовательских сценария нужны для безопасного выпуска.

## Implementation Strategy

После тестов изменить правило и проверить повторный пересчёт старой активированной и новой ожидающей строк. Исторические данные остаются без изменений.
