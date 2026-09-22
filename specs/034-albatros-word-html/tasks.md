# Задачи: импорт Word HTML протоколов Albatros-Timing

**Input**: Артефакты из `specs/034-albatros-word-html/`

## Phase 1: Подготовка

- [X] T001 Проверить фактическую кодировку, десять заголовков групп и 248 строк fixture в `storage/tests/2026/20260404.htm`.

## Phase 2: User Story 1 — импорт Word HTML протокола (P1) 🎯 MVP

**Цель**: production-протокол разбирается в строки результатов всех групп, включая
обычные результаты и `DSQ`/`DSQ6`.

**Независимый тест**: `vendor/bin/phpunit tests/Models/Parser/AlbatrosTimingParserTest.php`
с новым data-provider случаем `2026/20260404.htm`.

- [X] T002 [US1] Добавить в `tests/Models/Parser/AlbatrosTimingParserTest.php` red regression-кейс для fixture: 248 строк, обычные М1 и Ж5, а также `DSQ` и `DSQ6` с ожидаемыми группами, дистанцией и null-временем/местом.
- [X] T003 [US1] Запустить `vendor/bin/phpunit tests/Models/Parser/AlbatrosTimingParserTest.php` и зафиксировать red-состояние с текущим исключением Word HTML варианта.
- [X] T004 [US1] Нормализовать кодировку документа и собрать последовательные Word `pre`-строки каждого `h2` блока в `app/Models/Parser/AlbatrosTimingParser.php`, сохранив существующий public row contract.
- [X] T005 [US1] Запустить новый и все прежние случаи `tests/Models/Parser/AlbatrosTimingParserTest.php` в green-состоянии.

## Phase 3: User Story 2 — совместимость Albatros-Timing (P2)

**Цель**: ранее поддерживаемые Albatros-Timing-варианты не регрессируют.

**Независимый тест**: полный набор parser-тестов проходит с неизменёнными прежними
ожиданиями.

- [X] T006 [US2] Запустить `vendor/bin/phpunit tests/Models/Parser` и устранить только регрессии, связанные с Word HTML в `app/Models/Parser/AlbatrosTimingParser.php`.

## Phase 4: Полировка и гейты

- [X] T007 Выполнить проверки из `specs/034-albatros-word-html/quickstart.md`: `composer stan`, `composer cs` и `git diff --check`.
- [X] T008 Сверить `spec.md`, `plan.md`, `tasks.md`, контракт и checklist в `specs/034-albatros-word-html/`; отметить выполненные задачи.

## Зависимости и порядок

- T001 → T002 → T003 → T004 → T005 → T006 → T007 → T008.
- T002 и T003 фиксируют defect до изменения parser-а.
- MVP — завершение T005; T006 подтверждает обратную совместимость.

## Параллелизм

Для этого узкого parser bugfix параллельных задач нет: выбор ожидаемых строк, red
проверка и минимальная правка зависят друг от друга.
