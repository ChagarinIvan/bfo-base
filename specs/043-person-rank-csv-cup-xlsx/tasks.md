# Tasks: Экспорт разрядов и таблиц кубка

**Input**: [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), [contracts/export.md](contracts/export.md)

## Phase 1: Setup

- [X] T001 Проверить действующие фильтры и таблицы в `app/Application/Dto/Person/SearchPersonDto.php`, `app/Infrastructure/Laravel/Eloquent/Person/EloquentPersonRepository.php`, `app/Application/Dto/Cup/ExportCupTableDto.php` и `resources/spa/pages/persons/PersonsPage.vue`.

## Phase 2: Foundation

- [X] T002 [P] Добавить request-тест CSV персон в `tests/Feature/Api/V1/Person/ExportPersonRanksActionTest.php`.
- [X] T003 [P] Обновить request-тесты XLSX/HTML кубка в `tests/Feature/Api/V1/Cup/ExportCupTableActionTest.php`.
- [X] T004 [P] Обновить тесты кнопок и API запросов в `resources/spa/pages/persons/PersonsPage.test.ts`, `resources/spa/components/CupInfoNavigation.test.ts`, `resources/spa/api/persons.test.ts`, `resources/spa/api/cups.test.ts`.

## Phase 3: User Story 1 - CSV разрядов (P1)

- [X] T005 [US1] Создать `app/Domain/Person/PersonRankExportRow.php`, добавить `exportByCriteria()` в `app/Domain/Person/PersonRepository.php` и реализовать общий поиск и порционный экспорт в `app/Infrastructure/Laravel/Eloquent/Person/EloquentPersonRepository.php`.
- [X] T006 [US1] Создать `app/Application/Service/Person/ExportPersonRanks.php`, `app/Application/Service/Person/ExportPersonRanksService.php`, `app/Bridge/Laravel/Http/Serialization/PersonRanksCsvSerializer.php` и `app/Bridge/Laravel/Http/Controllers/Api/V1/Person/ExportPersonRanksAction.php`.
- [X] T007 [US1] Зарегистрировать маршрут и binding в `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php` и `app/Bridge/Laravel/Provider/Person/PersonProvider.php`.
- [X] T008 [US1] Добавить запрос, кнопку и перевод в `resources/spa/api/persons.ts`, `resources/spa/pages/persons/PersonsPage.vue`, `resources/lang/by.json`.

## Phase 4: User Story 2 - XLSX кубка (P1)

- [X] T009 [US2] Добавить XLSX сериализатор с листами и жирным зачётом в `app/Bridge/Laravel/Http/Serialization/CupTableXlsxSerializer.php`.
- [X] T010 [US2] Заменить CSV на XLSX в `app/Application/Dto/Cup/ExportCupTableRequestDto.php` и `app/Bridge/Laravel/Http/Serialization/CupTableExportResponseAssembler.php`.
- [X] T011 [US2] Обновить меню и запрос в `resources/spa/components/CupInfoNavigation.vue` и `resources/spa/api/cups.ts`.

## Phase 5: User Story 3 - Белорусский табличный формат (P2)

- [X] T012 [US3] Применить заголовки, порядок колонок и даты в `app/Bridge/Laravel/Http/Serialization/CupTableExportLayout.php`, `app/Bridge/Laravel/Http/Serialization/CupTableHtmlSerializer.php` и `app/Bridge/Laravel/Http/Serialization/CupTableXlsxSerializer.php`; запрашивать отбор нулевых групповых строк из `app/Application/Service/Cup/ExportCupTableService.php`.
- [X] T013 [US3] Проверить одинаковые значения XLSX и HTML, нулевые итоги и имена листов в `tests/Feature/Api/V1/Cup/ExportCupTableActionTest.php`, `tests/Application/Service/Cup/ExportCupTableServiceTest.php`, `tests/Bridge/Laravel/Http/Serialization/CupTableHtmlSerializerTest.php` и `tests/Bridge/Laravel/Http/Serialization/CupTableXlsxSerializerTest.php`.

## Final Phase: Validation

- [X] T014 Сверить [spec.md](spec.md), [contracts/export.md](contracts/export.md), [quickstart.md](quickstart.md) и чеклист с реализацией; выполнить узкие тесты, полные финальные гейты, проверку маршрутов и запросов.
- [X] T015 Обновить `resources/spa/pages/updates/updates.ts` и пункт 2 `plan.txt` после полного завершения.

## Follow-up: фильтр строк таблицы

- [X] T016 Перенести исключение нулевых строк в `app/Domain/Cup/Table/StandardCupTableBuilder.php`, добавить флаг в `app/Domain/Cup/Table/CupTableBuilder.php` и отдельный вариант кэша в `app/Infrastructure/Laravel/Cache/CachedCupTableBuilder.php`; проверить сохранение мест, вызов из сервиса и API.
- [X] T017 Убрать отдельные `PersonSearchQuery` и `EloquentPersonRankExportRepository`; перенести построение запроса и `exportByCriteria()` в `EloquentPersonRepository` и проверить список и CSV.
- [X] T018 Убрать `PersonRankExportRepository`, объявить `exportByCriteria()` в `PersonRepository` и перевести сервис экспорта на этот интерфейс.
- [X] T019 Защитить поля CSV персон, которые Excel может интерпретировать как формулы, в `app/Bridge/Laravel/Http/Serialization/PersonRanksCsvSerializer.php`; добавить request-тест и уточнить контракт.

## Dependencies

T001 предшествует тестам. T002–T004 предшествуют реализации. T005–T008 и T009–T011 можно выполнить независимо; T012–T013 требуют готового XLSX. T014–T015 завершают фичу.

T016 уточняет реализацию T012 после завершения основного цикла.
T017 упрощает реализацию T005 после завершения основного цикла.
T018 завершает объединение репозитория персон после T017.

## Independent tests

- US1: CSV по всем фильтрам, без ограничения страницы и без доступа гостя.
- US2: число листов, жирное выделение и замена CSV маршрута.
- US3: одинаковые белорусские колонки и значения, исключение нулевых итогов группы.

**MVP**: US1 и US2.

## Validation record

- 2026-10-09: PHPUnit прошёл 618 тестов и 4519 проверок. Полный запуск дал 17 notices; после него notice нового unit-теста устранён, а его узкий набор прошёл без notices. В истории предыдущей фичи зафиксировано 16 notices.
- 2026-10-09: Frontend CI прошёл 84 файла и 251 тест, TypeScript, ESLint, Prettier и production-сборку.
- 2026-10-09: PHPStan, PHP CS Fixer, Rector dry-run и `git diff --check` прошли. Маршрут `persons/export` зарегистрирован, сервер Laravel запущен, гостевой HTTP-запрос вернул 401. Выборка CSV читает только нужные поля порциями, кубок использует существующий кэш таблиц.
- 2026-10-09: После переноса фильтра в билдер прошли 5 узких unit-тестов (79 проверок), 10 API-тестов (68 проверок), PHPStan, PHP CS Fixer, Rector dry-run и `git diff --check`.
- 2026-10-09: После объединения реализации поиска и экспорта персон прошли 9 узких тестов репозитория и API (95 проверок), PHPStan, PHP CS Fixer, Rector dry-run и `git diff --check`.
- 2026-10-09: После удаления отдельного интерфейса экспорта полный PHPUnit прошёл 619 тестов и 4549 проверок, зафиксировано 15 notices. Frontend CI прошёл 84 файла и 251 тест, lint, typecheck и production-сборку. PHPStan, PHP CS Fixer, Rector dry-run и `git diff --check` прошли.
- 2026-10-09: Защитный префикс CSV проверен узким request-набором: 4 теста, 89 проверок. PHPStan, PHP CS Fixer, Rector dry-run и `git diff --check` прошли.
