# Задачи импорта гомельского спринта

## Подготовка

- [X] T001 [US1] Проверить кодировку, разметку и выбор парсера. Зафиксировать разницу с фичей 036 в `research.md`.

## Регрессия и исправление

- [X] T002 [US1] Скопировать исходный HTML в `storage/tests/2026/20261010-gomel-sprint.htm`. Добавить контрольные строки в `tests/Models/Parser/AlbatrosTimingParserTest.php` и воспроизвести сбой (FR-001–FR-003, SC-002).
- [X] T003 [US1] Исправить разбор колонки результата в `app/Models/Parser/AlbatrosTimingParser.php` (FR-002–FR-004).
- [X] T004 [US1] Запустить тест Albatros-Timing и весь набор парсеров. Сверить все 214 троек «Г.р.», «Квал», «Номер» с HTML (FR-001–FR-004, SC-001–SC-002).

## Завершение

- [X] T005 Добавить запись в `resources/spa/pages/updates/updates.ts` и обновить ожидаемую последнюю запись в `resources/spa/pages/updates/updates.test.ts`.
- [X] T006 Запустить проверки из `plan.md`, проверить дифф и сверить реализацию с критериями приёмки.

Порядок для парсера: T001 → T002 → T003 → T004. Для финальных проверок нужны T004 и T005.

## Результаты проверок

- `composer test` на `bfo_base_test`: 749 тестов без падений, 15 уведомлений PHPUnit.
- `composer cs`, `composer stan`, `composer rector -- --dry-run` и `git diff --check` прошли.
- `npm run ci` на Node.js 22 прошёл. Vite предупредил о размере сборки.
- Локальный Laravel ответил `200` на `GET /api/v1/groups`. Изменение парсера не добавляет запросов к БД.
