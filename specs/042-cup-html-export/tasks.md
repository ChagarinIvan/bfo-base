# Tasks: Экспорт таблицы кубка в CSV и HTML

**Input**: [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), [contracts/export.md](contracts/export.md)

## Phase 1: Setup

- [X] T001 Проверить текущий CSV, прежний HTML в истории и неизменность `ExportCupTableDto` в `app/Application/Dto/Cup/`.

## Phase 2: Foundation

- [X] T002 [P] Добавить запросные тесты выбора формата, ошибок и совпадения HTML/CSV с таблицей в `tests/Feature/Api/V1/Cup/ExportCupTableActionTest.php`.
- [X] T003 [P] Добавить компонентные и API-тесты меню и двух запросов в `resources/spa/components/CupInfoNavigation.test.ts` и `resources/spa/api/cups.test.ts`.

## Phase 3: User Story 1 - Выбрать формат полного экспорта (P1)

- [X] T004 [US1] Создать HTML-сериализатор общего DTO с экранированием и пустыми таблицами в `app/Bridge/Laravel/Http/Serialization/CupTableHtmlSerializer.php` и его узкие тесты в `tests/Bridge/Laravel/Http/Serialization/CupTableHtmlSerializerTest.php`.
- [X] T005 [US1] Добавить общий сборщик HTML/CSV attachment в `app/Bridge/Laravel/Http/Serialization/CupTableExportResponseAssembler.php` и оставить один вызов в `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/ExportCupTableAction.php`.
- [X] T006 [US1] Передать явный `format` из `resources/spa/api/cups.ts` и проверить оба значения.
- [X] T007 [US1] Заменить одиночное действие на доступное меню CSV/HTML в `resources/spa/components/CupInfoNavigation.vue`; добавить подписи в `resources/lang/by.json` и скачивание с нужным расширением.

## Phase 4: User Story 2 - Стабильный контракт скачивания (P2)

- [X] T008 [US2] Проверить безпараметрический CSV, неверный `format`, гостя, неизвестный и пустой кубок в `tests/Feature/Api/V1/Cup/ExportCupTableActionTest.php`.
- [X] T009 [US2] Проверить ошибки скачивания, отсутствие навигации и доступность меню в `resources/spa/components/CupInfoNavigation.test.ts`.

## Final Phase: Validation

- [X] T010 Добавить запись 042 в `resources/spa/pages/updates/updates.ts` и сверить `resources/spa/pages/updates/updates.test.ts`.
- [X] T011 Сверить [spec.md](spec.md), [contracts/export.md](contracts/export.md), [quickstart.md](quickstart.md) и [checklists/requirements.md](checklists/requirements.md) с реализацией; выполнить узкие тесты, финальные гейты, запуск приложения и проверку числа запросов.

## Dependencies

T001 предшествует T002–T004. T004–T005 предшествуют проверке API. T006–T007 предшествуют компонентной проверке. T010–T011 выполняются после обеих историй.

## Validation record

- 2026-10-07: CSV serialization adds UTF-8 BOM for Excel on Windows. Regression test failed before the fix and passed after it; API export tests passed (5 tests, 88 assertions). CSV rows, delimiter and CRLF remain unchanged after the BOM.
- 2026-10-07: HTML/CSV request tests passed (5 tests, 84 assertions); serializer unit test passed. The HTML and CSV rows match the JSON table for two groups. Warm HTML and CSV exports use the same number of SQL queries.
- `npm run ci` passed: 84 files, 247 tests and production build. `composer cs`, `composer stan` and `composer rector -- --dry-run` passed.
- `DB_DATABASE=bfo_base_test composer test` ran 614 tests and found one unrelated random primary-key collision in `StandardProtocolLineIdentifierTest`; that file passed separately (6 tests, 49 assertions). The group factory chooses IDs in `1..100`, so two groups can collide.
- `php artisan route:list --path=api/v1/cups` resolved the export route. The Laravel server started and an unauthenticated HTML export request returned JSON `401`.
- `git diff --check` passed. No new mutation, N+1 path or legacy route was introduced.
