# Задачи: таблица кубка в SPA

## Уточнения по пользовательскому ревью

- [X] Возвращать из API пагинированный Slice строк результата без контейнера `CupTable`.
- [X] Оставить данные карточки кубка и административные действия видимыми при загрузке таблицы.
- [X] Исключить заголовки этапов из списка настраиваемых колонок и восстановить видимость основных колонок.
- [X] Сохранить выделение зачётных результатов и обычный вид незачётных ссылок.
- [X] Загружать метаданные этапов один раз за время жизни страницы.

## Phase 1: Setup и обследование

- [X] T001 [P] Сверить старый Blade output и текущие cup type расчёты в `resources/views/cup/table.blade.php`, `app/Services/CupEventsService.php` и `app/Domain/Cup/CupType/`.
- [X] T002 [P] Зафиксировать текущие SPA routes/API/types в `resources/spa/router/index.ts`, `resources/spa/pages/cups/CupViewPage.vue` и `resources/spa/api/`.

## Phase 2: Foundational API contract

- [X] T003 Добавить V1 DTO и assembler для stage metadata, table rows и stage cells в `app/Application/Dto/Cup/`.
- [X] T004 Добавить Application query command/service для таблицы кубка в `app/Application/Service/Cup/`, изолировав существующий cup calculation и batch loading persons/clubs.
- [X] T005 Добавить Bridge action и маршрут `GET /api/v1/cups/{cupId}/tables/{groupId}` с обязательным `groupId` и camelCase `name` в `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/` и `ApiV1RoutesServiceProvider.php`.
- [X] T006 [P] Добавить API request tests с `@see`, проверяющие 200 contract, 404 и validation для короткого имени в `tests/Feature/Api/V1/Cup/CupTableActionTest.php`.

## Phase 3: User Story 1 — layout и вкладки (P1)

**Independent test**: карточка и нижний список/таблица переключаются через Vue Router без полной перезагрузки.

- [X] T007 [US1] Выделить layout карточки кубка и navigation tabs в `resources/spa/pages/cups/CupLayoutPage.vue` по образцу `resources/spa/pages/persons/PersonLayoutPage.vue` и `PersonInfoNavigation.vue`.
- [X] T008 [US1] Добавить child routes для `CupStagesPage.vue` и `CupTablePage.vue` в `resources/spa/router/index.ts`, сохранив текущие edit/create routes.
- [X] T009 [US1] Перенести текущий список этапов в `resources/spa/pages/cups/CupStagesPage.vue` и обновить card/group links в `resources/spa/pages/cups/CupViewPage.vue`.
- [X] T010 [US1] Добавить frontend tests на default tab, direct table route, tab switching and preserved cup card в `resources/spa/pages/cups/CupViewPage.test.ts`.

## Phase 4: User Story 2 — таблица и scoring (P1)

**Independent test**: выбранная группа отображает stage columns, totals, average, place и protocol links, совпадающие с legacy output.

- [X] T011 [US2] Добавить API client, TypeScript types и response mapper для cup table в `resources/spa/api/cups.ts`, `resources/spa/api/types.ts` и tests.
- [X] T012 [US2] Реализовать `resources/spa/pages/cups/CupTablePage.vue` с динамическими stage columns, person/club links, points highlighting и ссылками на protocol lines.
- [X] T013 [US2] Добавить component/model tests в `resources/spa/pages/cups/CupTablePage.test.ts` для строк, зачётных очков, пустых ячеек, totals/average/place и группы.
- [X] T014 [US2] Сопоставить backend rows с legacy Blade fixture/ручным примером и исправить расхождения в application mapping без изменения cup type rules.

## Phase 5: User Story 3 — фильтр, pagination и columns (P1)

**Independent test**: фильтр от трёх символов и column visibility работают без потери группы и stage columns.

- [X] T015 [US3] Добавить обязательный group select из `cup.groups`, debounced name filter с minimum 3 validation и AbortController/request identity в `CupTablePage.vue`.
- [X] T016 [US3] Расширить `ListingTable` или добавить table-specific column policy, чтобы stage columns были обязательными, а остальные сохраняли пользовательскую настройку.
- [X] T017 [US3] Добавить frontend tests на обязательную группу, короткий ввод без запроса, debounce, stale response и невозможность скрыть stage columns.
- [X] T018 [P] Добавить белорусские тексты для вкладок, фильтра, loading/error/empty/validation в `resources/lang/by.json` и обновить i18n tests при необходимости.

## Phase 6: User Story 4 — ошибки и compatibility (P2)

**Independent test**: API/404/empty errors отображаются в нижней области, карточка остаётся доступной, legacy links не ломаются.

- [X] T019 [US4] Добавить retry/error/empty/not-found states в `CupTablePage.vue` и route-level handling.
- [X] T020 [US4] Обновить group badges и legacy-to-SPA navigation без изменения legacy Blade route behavior в `resources/spa/pages/cups/CupViewPage.vue`.
- [X] T021 [US4] Добавить tests на retry, 404, empty cup/group и сохранение card state.

## Phase 7: Polish и quality gates

- [X] T022 [P] Обновить API/client/model documentation и соответствующие spec contracts после финализации response shape.
- [X] T023 Запустить изменённые backend/frontend tests, `composer cs`, `composer stan`, `composer rector -- --dry-run`, `npm run lint`, `npm run typecheck`, `npm run build:spa`.
- [X] T024 Запустить `composer test` и `npm run test -- --run`, проверить N+1/batch loading и сверить acceptance scenarios.

## Зависимости

T001/T002 → T003–T006 → T007–T010 → T011–T014 → T015–T018 → T019–T021 → T022–T024.
US1 зависит от API route shape только для direct table route; US2 зависит от
foundational API; US3 зависит от US2; US4 можно начинать после US1/US2.

## Параллелизм

- T001 и T002 независимы.
- T006 можно выполнять параллельно с T003–T005 после фиксации контракта.
- T010, T013 и T017 работают в разных тестовых файлах после соответствующих
  component boundaries.
- T018 и T022 независимы от backend implementation.

## MVP

MVP: T003–T005, T007–T017. Он даёт карточку с вкладками, рабочую таблицу,
полную таблицу, поиск от трёх символов и обязательные stage columns. T019–T024
завершают error/compatibility и quality gates.
