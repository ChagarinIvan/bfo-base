# Задачи

## История US1. Данные карточки в таблице кубка

- [X] T001 [US1] Воспроизвести расхождение полей, поиск, неизвестный год и устаревший кэш в `tests/Feature/Api/V1/Cup/ViewCupTableActionTest.php`. Проверить HTML и XLSX в `tests/Feature/Api/V1/Cup/ExportCupTableActionTest.php` (FR-001–FR-003).
- [X] T002 [US1] Использовать данные загруженной карточки в `app/Domain/Cup/Table/StandardCupTableBuilder.php` и обновить `tests/Domain/Cup/Table/StandardCupTableBuilderTest.php` (FR-001, FR-002).
- [X] T003 [US1] Обновить версию ключей в `app/Infrastructure/Laravel/Cache/CachedCupTableBuilder.php`, тесте декоратора и сценарии очистки в `tests/Feature/Cup/ElkPathCupEliteGroupsTest.php`. Обрабатывать `PersonInfoUpdated` в `app/Application/Handler/Cup/ClearCupCacheHandler.php` (FR-003).

## Проверка

- [X] T004 Сверить сценарии приёмки и загрузку связи на всех путях расчёта. Выполнить проверки из `quickstart.md` и записать результаты в этот файл.

T001 предшествует T002 и T003. T004 выполняется после прохождения регрессионных тестов.

## Результаты проверки

- `composer test` на `bfo_base_test` прошёл. 751 тест, 5775 проверок, 15 уведомлений PHPUnit.
- `composer cs`, `composer stan` и `composer rector -- --dry-run` прошли.
- `npm run ci` на Node.js 22 прошёл. 304 теста, Vite предупредил о размере чанка.
- Laravel запустился локально. Гостевой запрос экспорта вернул 401. Request-тесты подтвердили содержимое выгрузок с авторизацией.
- Запросы протоколов заранее загружают `person` или `person.club` во всех путях расчёта. Построитель не добавляет SQL-запросов для каждой строки. `git diff --check HEAD` прошёл.

## Phase 3: Convergence

- [X] T005 [US1] Добавить запись о таблицах кубков в `resources/spa/pages/updates/updates.ts` и проверить дату, порядок и ссылку в `resources/spa/pages/updates/updates.test.ts` (FR-004, missing).

Каталог воспроизвёл ошибку CI до добавления записи. Итоговый `npm run ci` прошёл с закрытой T005. Линт, проверка типов, 304 теста и сборка SPA прошли. Vite предупредил о размере чанка.
