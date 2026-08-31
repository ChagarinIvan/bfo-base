---

description: "Задачи реализации SPA-управления клубами"
---

# Tasks: управление клубами в SPA

**Input**: design-документы из `/specs/007-club-spa-management/`

**Prerequisites**: [plan.md](plan.md), [spec.md](spec.md), [research.md](research.md),
[data-model.md](data-model.md), [contracts/](contracts/), [quickstart.md](quickstart.md)

**Tests**: обязательны согласно FR-020, AR-005 и конституции: unit/application, API request и
frontend-тесты. До выполнения задач на удаление legacy должны быть зелёными тесты заменяющего
SPA/API-сценария.

**Organization**: задачи сгруппированы по пользовательским историям, чтобы каждый срез можно было
собрать и проверить отдельно.

## Формат: `[ID] [P?] [Story] Описание`

- **[P]**: задача может выполняться параллельно с другими задачами фазы, если они не меняют те же
  файлы.
- **[Story]**: пользовательская история из [spec.md](spec.md).
- Каждая задача содержит конкретные файлы, которые следует изменить, создать или удалить.

## Phase 1: Setup — аудит исходной точки

**Purpose**: зафиксировать реальные legacy consumers до массовых переименований и удаления.

- [X] T001 [P] Выполнить usage-аудит текущих club/person list DTO, services, Blade routes и club links в `app/Application/Dto/Club/`, `app/Application/Dto/Person/`, `app/Application/Service/Club/`, `app/Application/Service/Person/`, `app/Bridge/Laravel/`, `resources/views/` и `tests/`; использовать результат как allowlist для задач T002, T003 и T045–T049.

### Audit result for T001

- Текущие непагинированные club list symbols (`ClubSearchDto`, `ListClubs`, `ListClubsService`)
  используются в `RendersEventDistance`, `ShowEventAction`, `ShowEventDistanceAction`,
  `ShowCupEventGroupAction`, `ShowCupTableAction`, `ShowCreatePersonAction`, `ShowEditPersonAction`
  и старом `ShowClubsListAction`; последние club action/view consumers удаляются в US5, остальные
  сохраняются и переходят на `Legacy*` names.
- Текущие person list symbols (`PersonSearchDto`, `ListPersons`, `ListPersonsService`) используются
  в `FixYearCommand`, `PruneInactivePersonsCommand`, `ListPersonAction`, event/cup actions,
  `ShowClubAction` и person list service test. `ShowClubAction` удаляется вместе со старой club
  страницей; console, `/api/person(s)`, events и cups остаются legacy consumers.
- `ViewClubDto` остаётся общим DTO после удаления `normalizeName`: его сохраняемые consumers —
  person create/edit forms, event/cup views, `components/club-link.blade.php`, club application
  services и их tests. `RendersEventDistance` должен вычислять нормализованный ключ из `name`.
- Полное текущее `ViewPersonDto` используется в `PersonAssembler`, add/list/update/view person
  services, event/cup views, person/payment/rank views и legacy person tests; оно переименовывается
  в `LegacyViewPersonDto`. Компактный `ViewPersonDto` предназначен только для нового V1 persons
  path.
- Club route/link cleanup затрагивает `WebRoutesServiceProvider`, `ViewProvider`, club controllers,
  `resources/views/clubs/`, `resources/views/layouts/navbar.blade.php`, event/cup Blade links,
  `resources/vue/components/person/Persons.vue`, `resources/spa/components/navigationModels.ts`
  и соответствующие tests. `resources/views/components/club-link.blade.php` сохраняется, пока есть
  `@include` из `resources/views/cup/events/show.blade.php`, но его href переводится в SPA.
- В переводах удаляются только obsolete keys старого club UI (`app.club.name`,
  `app.club.persons_count`, `app.clubs.create.title`, `app.clubs.name`) после повторной проверки;
  `app.navbar.clubs` и `spa.nav.clubs` сохраняются для работающих navbar/SPA.

---

## Phase 2: Foundational — общие контракты и безопасная совместимость

**Purpose**: подготовить именованные legacy пути и общие SPA utilities, не меняя работающие legacy
consumer contracts.

**⚠️ CRITICAL**: завершить эту фазу до реализации новых V1 read paths.

- [ ] T002 Переименовать непагинированные club list input/use case/service в `LegacySearchClubDto`, `ListLegacyClubs` и `ListLegacyClubsService` в `app/Application/Dto/Club/` и `app/Application/Service/Club/`, затем обновить все фактические consumers в `app/Bridge/Laravel/`, `app/Application/`, `app/Infrastructure/` и `tests/`.
- [ ] T003 Переименовать непагинированные person list input/use case/service в `LegacySearchPersonDto`, `ListLegacyPersons` и `ListLegacyPersonsService` в `app/Application/Dto/Person/` и `app/Application/Service/Person/`, затем обновить consumers в `app/Bridge/Laravel/`, `app/Bridge/Laravel/Console/`, `app/Infrastructure/Integration/` и `tests/`.
- [ ] T004 В `app/Application/Dto/Person/ViewPersonDto.php` создать компактный V1 DTO, перенести прежнее полное представление в `app/Application/Dto/Person/LegacyViewPersonDto.php`, разделить mapping в `app/Application/Dto/Person/PersonAssembler.php` и обновить реальные legacy consumers и их тесты в `app/Application/`, `app/Bridge/Laravel/`, `resources/views/` и `tests/`.
- [ ] T005 Удалить `normalizeName` из существующего `app/Application/Dto/Club/ViewClubDto.php` и `app/Application/Dto/Club/ClubAssembler.php`; в `app/Bridge/Laravel/Http/Controllers/Event/RendersEventDistance.php` строить нормализованный индекс из `ViewClubDto::name` через `NormalizedNameClubFinder`.
- [ ] T006 [P] Выделить общие name-search, debounce, reset-page, pagination-header и 422 field-error helpers из `resources/spa/pages/competitions/competitionModels.ts` в `resources/spa/pages/listingModels.ts`, сохранив обратную совместимость соревнований и обновив `resources/spa/pages/competitions/competitionModels.test.ts`.
- [ ] T007 [P] Добавить общие Club/Person V1 типы и pagination parsing в `resources/spa/api/types.ts`, не меняя существующие Competition contracts.

**Checkpoint**: legacy пути имеют явные имена, новый DTO персоны не конфликтует со старым, а SPA
может переиспользовать единое поведение поиска и ошибок.

---

## Phase 3: User Story 1 — найти клуб (Priority: P1) 🎯 MVP

**Goal**: публичный постраничный SPA-список активных клубов с поиском по названию.

**Independent Test**: без авторизации открыть `/app/clubs`, перейти по страницам, проверить
active-only count и порядок `name/id`; ввести 1–2, затем 3+ символа, включая пробелы и разный
регистр, и убедиться в debounce, field error и сбросе страницы.

### Tests for User Story 1

- [ ] T008 [P] [US1] Дополнить `tests/Application/Service/Club/ListClubsServiceTest.php` тестами paginated `Slice`, trim/minimum name и mapping активного `personsCount`.
- [ ] T009 [P] [US1] Создать `tests/Feature/Api/V1/Club/ListClubsActionTest.php` для public/auth projection, pagination headers, camelCase `perPage`, filter 422 `field=name`, stable name/id ordering, active-only count и query-count без N+1 для одной и нескольких строк.
- [ ] T010 [P] [US1] Создать `resources/spa/api/clubs.test.ts` и `resources/spa/pages/clubs/ClubsPage.test.ts` для request query, loading/empty/error, debounce, minimum-length hint, field errors, page reset и stale-response guard.

### Implementation for User Story 1

- [ ] T011 [US1] Создать `app/Application/Dto/Club/SearchClubDto.php`, `app/Application/Service/Club/ListClubs.php` и `app/Application/Service/Club/ListClubsService.php` для V1 paginated search по `name` без использования legacy array path.
- [ ] T012 [US1] Расширить `app/Domain/Club/ClubRepository.php` paginated read contract и реализовать constrained active-person count, case-insensitive name search, stable `name/id` order и `Slice` в `app/Infrastructure/Laravel/Eloquent/Club/EloquentClubRepository.php`.
- [ ] T013 [US1] Создать `app/Bridge/Laravel/Http/Controllers/Api/V1/Club/ListClubsAction.php` и зарегистрировать public optional-auth GET `/api/v1/clubs` в `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`.
- [ ] T014 [US1] Создать `resources/spa/api/clubs.ts` с list-запросом `/clubs` и pagination headers, используя типы из `resources/spa/api/types.ts`.
- [ ] T015 [US1] Создать `resources/spa/pages/clubs/ClubsPage.vue` и `resources/spa/pages/clubs/clubModels.ts` с единым name filter, PrimeVue пагинацией, состояниями loading/empty/general error и authenticated impressions.
- [ ] T016 [US1] Зарегистрировать `/app/clubs` и fallback-навигацию в `resources/spa/router/index.ts`, обновить пункт Clubs в `resources/spa/components/AppLayout.vue` и `resources/spa/components/navigationModels.ts`, затем обновить router/layout тесты в `resources/spa/router/index.test.ts` и `resources/spa/components/AppLayout.test.ts`.

**Checkpoint**: US1 полностью работает через новый API и SPA без авторизации; список может быть
демонстрирован как MVP.

---

## Phase 4: User Story 2 — просмотреть клуб и его персонов (Priority: P1)

**Goal**: публичная SPA-страница active клуба с compact paginated persons и legacy deep links.

**Independent Test**: открыть клуб с несколькими active/inactive персонами и клуб без персонов;
проверить detail, count, порядок фамилия/имя/id, pagination, public/auth impressions, 404 и переход
на `/persons/{id}/show`.

### Tests for User Story 2

- [ ] T017 [P] [US2] Дополнить `tests/Application/Service/Club/ViewClubServiceTest.php` и `tests/Application/Service/Person/ListPersonsServiceTest.php` проверками compact projections, optional `clubId`, active parent club при переданном фильтре и отсутствия rich person fields.
- [ ] T018 [P] [US2] Создать `tests/Feature/Api/V1/Club/ViewClubActionTest.php` и `tests/Feature/Api/V1/Person/ListPersonsActionTest.php` для public/auth serialization groups, 404 club detail, optional camelCase `clubId`, общей active-person выдачи без фильтра, active-only выдачи по клубу, stable lastname/firstname/id ordering, pagination headers и query-count без N+1.
- [ ] T019 [P] [US2] Создать `resources/spa/pages/clubs/ClubDetailsPage.test.ts` для detail/person loading, empty/not-found/error states, pagination, birthYear, impressions и обычного legacy person href.

### Implementation for User Story 2

- [ ] T020 [US2] Адаптировать существующие `app/Application/Service/Club/ViewClub.php` и `app/Application/Service/Club/ViewClubService.php` к V1 target contract с единственным очищенным `app/Application/Dto/Club/ViewClubDto.php`; `LegacyViewClubDto` не создавать и не вводить второй club view path.
- [ ] T021 [US2] Создать `app/Application/Dto/Person/SearchPersonDto.php`, `app/Application/Service/Person/ListPersons.php` и `app/Application/Service/Person/ListPersonsService.php` для компактного paginated V1 списка с опциональным `clubId` и возможностью будущих V1 filters.
- [ ] T022 [US2] Расширить `app/Domain/Person/PersonRepository.php` и `app/Infrastructure/Laravel/Eloquent/Person/EloquentPersonRepository.php` paginated read path: active person, active parent club при переданном `clubId`, order lastname/firstname/id, без payments/protocol lines и с `Slice`.
- [ ] T023 [US2] Создать `app/Bridge/Laravel/Http/Controllers/Api/V1/Club/ViewClubAction.php` и `app/Bridge/Laravel/Http/Controllers/Api/V1/Person/ListPersonsAction.php`; зарегистрировать optional-auth GET `/api/v1/clubs/{clubId}` и `/api/v1/persons` в `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`.
- [ ] T024 [US2] Расширить `resources/spa/api/clubs.ts` detail-запросом и создать `resources/spa/api/persons.ts` для `clubId` paginated request с типами и errors из `resources/spa/api/types.ts`.
- [ ] T025 [US2] Создать `resources/spa/pages/clubs/ClubDetailsPage.vue` с независимой person pagination, compact person table, `ImpressionDetails`, пустым/404/general-error состояниями и href `/persons/{id}/show`.
- [ ] T026 [US2] Зарегистрировать `/app/clubs/:id` в `resources/spa/router/index.ts` и дополнить `resources/spa/router/index.test.ts` публичной detail-навигацией.

**Checkpoint**: US1 и US2 работают как публичный путь «найти клуб → посмотреть active persons»;
глубокие person pages не затронуты.

---

## Phase 5: User Story 3 — создать клуб (Priority: P2)

**Goal**: аутентифицированная SPA-форма создания с нормализованной уникальностью и полевыми
ошибками.

**Independent Test**: войти, создать unique club и перейти на его detail; проверить required, max,
normalized duplicate, 401 и отсутствие общей ошибки вместо ошибки `name`.

### Tests for User Story 3

- [ ] T027 [P] [US3] Дополнить `tests/Domain/Club/Factory/PreventDuplicateClubFactoryTest.php` и `tests/Application/Service/Club/AddClubServiceTest.php` нормализованными duplicate cases и созданием с trimmed name.
- [ ] T028 [P] [US3] Создать `tests/Feature/Api/V1/Club/CreateClubActionTest.php` для 201 authenticated create, public 401, required/max validation и normalized duplicate 422 `field=name`.
- [ ] T029 [P] [US3] Создать `resources/spa/pages/clubs/ClubForm.test.ts` и `resources/spa/pages/clubs/CreateClubPage.test.ts` для guard, submit, field errors, сохранения ввода и перехода на detail после 201.

### Implementation for User Story 3

- [ ] T030 [US3] Изменить `app/Domain/Club/Factory/PreventDuplicateClubFactory.php`, `app/Domain/Club/Factory/ClubNameNormalizer.php` и связанные Club factory inputs так, чтобы create проверял duplicate по normalized name и сохранял нормализованное значение.
- [ ] T031 [US3] Добавить optional `field` в `app/Application/Exception/HttpError.php` и сериализацию этого поля в `app/Bridge/Laravel/Http/Serialization/ApiErrorResponse.php`, сохранив формат существующих ошибок без поля.
- [ ] T032 [US3] Привязать create business conflict к `name` в `app/Application/Service/Club/Exception/FailedToAddClub.php` и использовать его в `app/Application/Service/Club/AddClubService.php`.
- [ ] T033 [US3] Создать `app/Bridge/Laravel/Http/Controllers/Api/V1/Club/CreateClubAction.php` и зарегистрировать authenticated POST `/api/v1/clubs` в `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`.
- [ ] T034 [US3] Дополнить `resources/spa/api/clubs.ts` create-запросом и создать общую `resources/spa/pages/clubs/ClubForm.vue` с name validation/error state и форматом дат `YYYY-MM-DD` через `resources/spa/components/ImpressionDetails.vue`.
- [ ] T035 [US3] Создать `resources/spa/pages/clubs/CreateClubPage.vue`, зарегистрировать guarded `/app/clubs/create` в `resources/spa/router/index.ts` и добавить authenticated действие «Добавить клуб» в `resources/spa/pages/clubs/ClubsPage.vue`.

**Checkpoint**: аутентифицированный пользователь может создать клуб исключительно через SPA/V1, а
duplicate и validation отображаются под полем.

---

## Phase 6: User Story 4 — отредактировать клуб (Priority: P2)

**Goal**: аутентифицированное переименование active клуба с теми же правилами, что при создании.

**Independent Test**: открыть `/app/clubs/{id}/edit`, изменить name, увидеть его на detail/list;
проверить same-normalized self update, duplicate, invalid, 401 и 404/inactive cases.

### Tests for User Story 4

- [ ] T036 [P] [US4] Создать `tests/Application/Service/Club/UpdateClubInfoServiceTest.php` и `tests/Domain/Club/ClubTest.php` для atomic rename, updated impression, self-exclusion, normalized duplicate и записи `ClubInfoUpdated` aggregate event.
- [ ] T037 [P] [US4] Создать `tests/Feature/Api/V1/Club/UpdateClubActionTest.php` для authenticated 200, 401, 404 inactive/missing, validation и 422 `field=name` без изменения данных.
- [ ] T038 [P] [US4] Создать `resources/spa/pages/clubs/EditClubPage.test.ts` для prefilled form, guard, PUT field errors и перехода на detail после update.

### Implementation for User Story 4

- [ ] T039 [US4] Создать `app/Domain/Club/ClubInfo.php` и `app/Domain/Club/Event/ClubInfoUpdated.php`; перевести `app/Domain/Club/Club.php` на существующий aggregate pattern и добавить `updateInfo(ClubInfo, Impression)`, который обновляет name/normalize_name/updated и записывает event, без `ClubUpdater` или provider binding.
- [ ] T040 [US4] Создать `app/Application/Service/Club/UpdateClubInfo.php`, `app/Application/Service/Club/UpdateClubInfoService.php` и необходимые application exceptions в `app/Application/Service/Club/Exception/` для lock/update/not-found/name conflict.
- [ ] T041 [US4] Создать `app/Bridge/Laravel/Http/Controllers/Api/V1/Club/UpdateClubAction.php`, зарегистрировать authenticated PUT `/api/v1/clubs/{clubId}` в `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`, добавить update-запрос в `resources/spa/api/clubs.ts` и реализовать `resources/spa/pages/clubs/EditClubPage.vue` на общей `ClubForm.vue`.
- [ ] T042 [US4] Добавить guarded `/app/clubs/:id/edit`, edit action на `resources/spa/pages/clubs/ClubDetailsPage.vue` и проверки route guard в `resources/spa/router/index.ts` и `resources/spa/router/index.test.ts`.

**Checkpoint**: create и edit используют одну форму и одни доменные правила, а detail/list сразу
показывают актуальное имя и authenticated impressions.

---

## Phase 7: User Story 5 — завершить миграцию клубных страниц (Priority: P3)

**Goal**: все поддерживаемые входы в клубы ведут в SPA, старый UI удалён, а необходимые legacy
scenarios остаются рабочими.

**Independent Test**: перейти по legacy navbar, event/cup/person links и старым GET URLs; увидеть
SPA/301, убедиться, что POST store отсутствует, а person pages, `/api/person`, `/api/persons`,
console и используемые legacy forms не регрессировали.

### Tests for User Story 5

- [ ] T043 [P] [US5] Создать `tests/Feature/Api/V1/Club/LegacyClubRoutesTest.php` для 301 `/clubs`, `/clubs/create`, `/clubs/{clubId}/show`, отсутствующего POST `/clubs/store` и отсутствия Blade render/business logic.
- [ ] T044 [P] [US5] Обновить `tests/Bridge/Laravel/Http/Controllers/Person/ShowPersonActionTest.php`, `tests/Bridge/Laravel/Http/Controllers/Api/ListPersonActionTest.php` и `tests/Bridge/Laravel/Http/Controllers/Api/PersonControllerTest.php` для сохранения rich legacy projection и deep person paths после rename.

### Implementation for User Story 5

- [ ] T045 [US5] Заменить club GET routes в `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php` на permanent redirects `/clubs` → `/app/clubs`, `/clubs/create` → `/app/clubs/create`, `/clubs/{clubId}/show` → `/app/clubs/{clubId}` и удалить POST `/clubs/store`.
- [ ] T046 [US5] Удалить заменённые Blade actions и tests: `app/Bridge/Laravel/Http/Controllers/Club/ClubAction.php`, `ShowClubsListAction.php`, `ShowCreateClubFormAction.php`, `ShowClubAction.php`, `StoreClubsAction.php`, `tests/Bridge/Laravel/Http/Controllers/Club/ShowClubsListActionTest.php`, `ShowClubActionTest.php`, `StoreClubsActionTest.php`.
- [ ] T047 [US5] Удалить заменённые Blade views `resources/views/clubs/index.blade.php`, `resources/views/clubs/create.blade.php`, `resources/views/clubs/show.blade.php` и только obsolete club-translation keys из `resources/lang/by.json` и `resources/lang/ru.json`; сохранить `app.navbar.clubs` и `spa.nav.clubs` после поиска оставшихся consumers.
- [ ] T048 [US5] Перевести legacy navbar и все найденные club links на SPA в `resources/views/layouts/navbar.blade.php`, `resources/views/components/club-link.blade.php`, `resources/views/events/`, `resources/views/cup/`, `resources/vue/components/person/Persons.vue` и других найденных consumers; сохранить person deep links и partial `club-link.blade.php`, если он остаётся подключён через `@include`.
- [ ] T049 [US5] После повторного usage-аудита удалить только подтверждённо мёртвые `app/Bridge/Laravel/View/Components/ClubLink.php`, его registration в `app/Bridge/Laravel/Provider/ViewProvider.php`, и `app/Application/Service/Club/DisableClub.php` с `DisableClubService.php` и `tests/Application/Service/Club/DisableClubServiceTest.php`; сохранить любой артефакт с оставшимся consumer.

**Checkpoint**: второй club UI удалён, старые GET-ссылки сохраняют bookmarks через 301, а
используемые legacy person/event/cup/console paths работают через явно именованные adapters.

---

## Phase 8: Polish & cross-cutting validation

**Purpose**: подтвердить контракты, отсутствие регрессий и Definition of Done.

- [ ] T050 [P] Проверить [quickstart.md](quickstart.md) вручную: public/auth list/detail/persons, forms, field errors, `YYYY-MM-DD`, redirects и legacy compatibility; зафиксировать отклонения в `specs/007-club-spa-management/quickstart.md` только если контракт изменился осознанно.
- [ ] T051 [P] Выполнить финальный поиск удалённых club Blade routes/actions/views и устаревших `ViewPersonDto` usages в `app/`, `resources/` и `tests/`; сверить оставшиеся legacy consumers с `specs/007-club-spa-management/data-model.md`.
- [ ] T052 Выполнить frontend quality gates для изменённых `resources/spa/`: `npm run ci` и targeted Vitest suites `resources/spa/api/clubs.test.ts` и `resources/spa/pages/clubs/`.
- [ ] T053 Выполнить backend quality gates: `vendor/bin/phpunit tests/Feature/Api/V1/Club tests/Feature/Api/V1/Person tests/Application/Service/Club tests/Application/Service/Person`, затем `composer cs`, `composer stan`, `composer rector -- --dry-run`, `composer test` и `git diff --check`.

---

## Dependencies & execution order

### Phase dependencies

- **Phase 1**: начинается немедленно и определяет фактические consumers для безопасного rename/delete.
- **Phase 2**: зависит от T001 и блокирует новые V1 read paths.
- **US1 (Phase 3)**: зависит от T002, T005–T007; это MVP.
- **US2 (Phase 4)**: зависит от T003–T005 и может идти после Phase 2 параллельно с US1 backend work,
  но SPA detail ссылается на list route из US1.
- **US3 (Phase 5)**: зависит от US1 list/detail API и общих SPA helpers.
- **US4 (Phase 6)**: зависит от общей формы и create domain rules из US3.
- **US5 (Phase 7)**: зависит от готовности US1–US4 и зелёных заменяющих тестов.
- **Polish (Phase 8)**: зависит от всех требуемых пользовательских историй.

### User story dependencies

- **US1 (P1)**: независимый MVP после foundation.
- **US2 (P1)**: использует общий foundation; SPA-навигация дополняет US1, API часть независима.
- **US3 (P2)**: требует публичный view для redirect после создания.
- **US4 (P2)**: требует `ClubForm` и create validation из US3.
- **US5 (P3)**: требует SPA replacement всех four routes и audit consumers.

## Parallel opportunities

- T006 и T007 выполняются параллельно с T002–T005, если не меняют общие import/type files.
- В US1 тесты T008–T010 можно начать параллельно; после их фиксации T011–T013 — backend цепочка,
  а T014–T016 — frontend цепочка.
- В US2 T017–T019 параллельны; T021–T023 и T024–T026 разделяются между backend и frontend после
  согласования contracts.
- В US3 T027–T029 параллельны; T030–T033 — backend, T034–T035 — frontend.
- В US4 T036–T038 параллельны; T039–T041 — backend/form, T042 — router integration.
- В US5 T043 и T044 параллельны; T045–T049 выполнять после повторного usage-аудита и без параллельных
  изменений тех же routes/views/tests.

## Parallel example: User Story 1

```text
Task: "T008 application tests in tests/Application/Service/Club/ListClubsServiceTest.php"
Task: "T009 API request tests in tests/Feature/Api/V1/Club/ListClubsActionTest.php"
Task: "T010 frontend tests in resources/spa/api/clubs.test.ts and resources/spa/pages/clubs/ClubsPage.test.ts"
```

## Implementation strategy

### MVP first

1. Выполнить Phase 1 и Phase 2.
2. Выполнить US1 и его focused tests.
3. Проверить публичный `/app/clubs` и `GET /api/v1/clubs` по критерию US1.
4. Не удалять Blade до готовности US2–US5.

### Incremental delivery

1. US1 даёт безопасный read-only SPA entry point.
2. US2 добавляет ценность detail/persons без переноса person pages.
3. US3 и US4 завершают аутентифицированный management flow.
4. US5 удаляет только заменённый UI после подтверждения всех ссылок и consumers.
