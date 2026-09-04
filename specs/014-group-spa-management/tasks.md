---
description: "Задачи реализации управления группами в SPA"
---

# Задачи: управление группами в SPA

**Input**: [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md),
[data-model.md](data-model.md), [contracts/api.md](contracts/api.md),
[quickstart.md](quickstart.md)

**Tests**: обязательны для изменяемого поведения согласно FR-018 и конституции проекта.

## Phase 1: Setup — аудит и фиксация исходной точки

**Цель**: зафиксировать текущие usages и подготовить безопасную миграцию без потери shared-сценариев.

- [ ] T001 [P] Провести аудит group routes, Blade views и внутренних ссылок через `rg` в `app/`, `resources/`, `tests/`; зафиксировать targets для redirect/delete в `specs/014-group-spa-management/research.md`.
- [ ] T002 [P] Провести аудит usages `GroupsRepository`, `GroupsService`, `DistanceService` и group model в `app/Domain/`, `app/Application/`, `app/Bridge/`, `app/Infrastructure/`, `tests/`; отметить shared consumers в `specs/014-group-spa-management/research.md`.
- [ ] T003 [P] Проверить текущую схему `groups`/`distances`, существующие FK/index и SQL для count/order; зафиксировать решение по дополнительному индексу в `specs/014-group-spa-management/research.md`.

## Phase 2: Foundational — общие порты, сериализация и audit storage

**Цель**: подготовить блокирующие контракты и целевую архитектуру до вертикальных срезов.

- [X] T004 [P] Добавить обратимую миграцию audit columns `created_at`, `created_by`, `updated_at`, `updated_by` для `groups` с backfill существующих строк в `database/migrations/*_add_group_impression.php`; отдельной миграцией `database/migrations/2026_09_04_000001_normalize_existing_group_names.php` пересчитать исторические `normalize_name` через `GroupNameNormalizer`.
- [X] T005 [P] Создать чистый group read/write contract и criteria types в `app/Domain/Group/GroupRepository.php` и связанных `app/Domain/Group/*`, не добавляя Eloquent в новые Domain value objects.
- [X] T006 Реализовать Eloquent group adapter с pagination, `withCount('distances')`, stable order, escaped name search и lock methods в `app/Infrastructure/Laravel/Eloquent/Group/EloquentGroupRepository.php`.
- [X] T007 [P] Добавить `created`/`updated` casts, fillable/aggregate behavior и normalized-name support для `app/Domain/Group/Group.php` через существующие `Impression`/`ImpressionCast` patterns.
- [X] T008 [P] Расширить event search DTO, criteria mapping и serializer contract для `groupId`, `withCompetition`, `competitionName`, `year` и `date` в `app/Application/Dto/Event/SearchEventDto.php`, `app/Application/Dto/Event/ViewEventDto.php` и `app/Application/Dto/Event/EventAssembler.php`.
- [X] T009 Добавить event repository query по group/competition filters с eager loading competition и distinct stable pagination в `app/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepository.php`; сохранить старые competition queries.
- [X] T010 Зарегистрировать group read/mutation API routes, optional/auth middleware и dependency bindings в `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php` и проектных providers.

**Checkpoint**: audit storage, group port/adapter и совместимый event API foundation готовы.

## Phase 3: User Story 1 — найти и открыть группу (Priority: P1) 🎯 MVP

**Goal**: публичный paginated group list с поиском от одного символа и стабильной сортировкой.

**Independent Test**: public request к `/api/v1/groups` и SPA `/app/groups` показывает только текущую
страницу, `distancesCount DESC, id ASC`, debounce-search и auth-aware projection.

### Tests for User Story 1

- [ ] T011 [P] [US1] Добавить API request tests для public/auth `ViewGroupDto`, pagination headers, min-one-character validation, stable count/id ordering и query count в `tests/Feature/Api/V1/Group/ListGroupsActionTest.php`.
- [ ] T012 [P] [US1] Добавить Application/Domain unit tests для group criteria, count ordering и public/auth serialization в `tests/Application/Service/Group/ListGroupsServiceTest.php` и `tests/Bridge/Laravel/Http/Serialization/ApiDtoSerializerTest.php`.
- [ ] T013 [P] [US1] Добавить API и helper tests для `groups.ts`, query trim, pagination metadata, debounce/minimum-length/reset behavior в `resources/spa/api/groups.test.ts` и `resources/spa/pages/groups/groupModels.test.ts`.
- [ ] T014 [P] [US1] Добавить component test list loading/empty/error, public/auth columns, stale-response guard, search debounce и paginator в `resources/spa/pages/groups/GroupsPage.test.ts`.

### Implementation for User Story 1

- [X] T015 [US1] Создать `ViewGroupDto`, group assembler и paginated list use case в `app/Application/Dto/Group/ViewGroupDto.php`, `app/Application/Dto/Group/GroupAssembler.php`, `app/Application/Service/Group/ListGroups.php` и `app/Application/Service/Group/ListGroupsService.php`.
- [X] T016 [US1] Создать public list API action в `app/Bridge/Laravel/Http/Controllers/Api/V1/Group/ListGroupsAction.php` с `Slice`/serializer behavior проекта; detail action создаётся в T023.
- [X] T017 [US1] Создать `resources/spa/api/groups.ts`, `resources/spa/pages/groups/groupModels.ts` и типы `Group`/`GroupSearchQuery` в `resources/spa/api/types.ts`.
- [X] T018 [US1] Создать публичный listing групп в `resources/spa/components/GroupListingTable.vue` и подключить его в `resources/spa/pages/groups/GroupsPage.vue` с FilterPanel, debounce, paginator, loading/error/empty states и auth-only actions.
- [X] T019 [P] [US1] Добавить public `/app/groups` route и navigation item в `resources/spa/router/index.ts` и `resources/spa/components/navigationModels.ts`; обновить i18n keys в `resources/lang/ru.json`, `resources/lang/by.json`, `resources/lang/en.json`.

**Checkpoint**: US1 демонстрирует поиск и открытие группы без legacy Blade page.

## Phase 4: User Story 2 — просмотреть группу и её старты (Priority: P1)

**Goal**: info table группы и paginated starts table на существующем event API с тремя фильтрами.

**Independent Test**: public `GET /api/v1/groups/{id}` + `GET /api/v1/events?groupId={id}&withCompetition=1`
возвращают корректные group/start projections, фильтры и pagination без action columns.

### Tests for User Story 2

- [ ] T020 [P] [US2] Создать `tests/Feature/Api/V1/Group/ViewGroupActionTest.php` для 200/404, public/auth impressions и `distancesCount`.
- [ ] T021 [P] [US2] Расширить `tests/Feature/Api/V1/Event/ListEventsActionTest.php` для `groupId`, `withCompetition`, competition name/year/date filters, distinct events, stable pagination и сохранения старого `competitionId` behavior.
- [ ] T022 [P] [US2] Создать `resources/spa/api/events.test.ts`/дополнить его group query assertions и `resources/spa/pages/groups/GroupDetailsPage.test.ts` для info, starts, filters, paginator, empty/not-found/error и отсутствия actions.

### Implementation for User Story 2

- [X] T023 [US2] Создать group detail use case/action и DTO loading path в `app/Application/Service/Group/ViewGroup.php`, `app/Application/Service/Group/ViewGroupService.php` и `app/Bridge/Laravel/Http/Controllers/Api/V1/Group/ViewGroupAction.php`.
- [X] T024 [US2] Расширить `app/Application/Dto/Event/ViewEventDto.php`, `app/Application/Dto/Event/EventAssembler.php` и `app/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepository.php` для optional competition projection, group relation filter, competition name/year/date filters и N+1-safe query.
- [X] T025 [US2] Расширить `resources/spa/api/events.ts` и `resources/spa/api/types.ts` для `groupId`, `withCompetition`, competition filters и paginated start responses.
- [X] T026 [US2] Создать `resources/spa/pages/groups/GroupDetailsPage.vue` с верхней info table, starts table на PrimeVue, competition name/year/date filters, pagination and no action column.
- [X] T027 [P] [US2] Добавить маршруты `/app/groups/:id` и публичный SPA fallback/legacy href behavior в `resources/spa/router/index.ts` и связанные i18n/css файлы.

**Checkpoint**: US1 и US2 вместе покрывают публичный list/detail пользовательский путь.

## Phase 5: User Story 3 — редактировать и удалить группу (Priority: P2)

**Goal**: auth-only edit с duplicate prevention и delete popup с атомарным удалением зависимостей.

**Independent Test**: authenticated API/UI edit/delete; duplicate, validation, cancel и successful
delete scenarios оставляют консистентные данные.

### Tests for User Story 3

- [X] T028 [P] [US3] Создать Domain/Application unit tests для normalizer, duplicate factory/updater, idempotent self-update и delete command без Eloquent в `tests/Domain/Group/` и `tests/Application/Service/Group/`.
- [ ] T029 [P] [US3] Создать API request tests для authenticated/unauthenticated update/delete, 422, 404, 409 `group_name_already_exists`, atomic dependency deletion и audit impressions в `tests/Feature/Api/V1/Group/UpdateGroupActionTest.php` и `tests/Feature/Api/V1/Group/DeleteGroupActionTest.php`.
- [ ] T030 [P] [US3] Создать SPA tests для edit form, duplicate/field errors, auth guard, delete confirmation/cancel/pending/success в `resources/spa/pages/groups/EditGroupPage.test.ts`, `resources/spa/pages/groups/GroupForm.test.ts` и `resources/spa/components/actions/ConfirmDeleteDialog.test.ts`.

### Implementation for User Story 3

- [X] T031 [US3] Создать group name normalizer/input, duplicate-preventing factory/updater и domain conflict в `app/Domain/Group/Factory/`, `app/Domain/Group/GroupInfo.php`, `app/Domain/Group/GroupUpdater.php` и `app/Domain/Group/Exception/`.
- [X] T032 [US3] Создать edit/delete commands и Application services с transaction boundary, auth impression и 409 mapping в `app/Application/Service/Group/UpdateGroupInfo.php`, `UpdateGroupInfoService.php`, `DeleteGroup.php`, `DeleteGroupService.php`.
- [X] T033 [US3] Создать `UpdateGroupAction`, `DeleteGroupAction` и request/response mapping в `app/Bridge/Laravel/Http/Controllers/Api/V1/Group/`.
- [X] T034 [US3] Создать `resources/spa/pages/groups/GroupForm.vue`, `EditGroupPage.vue`, action menu and delete dialog integration; добавить authenticated routes в `resources/spa/router/index.ts`.
- [X] T035 [P] [US3] Добавить group edit/delete i18n messages и единые action labels в `resources/lang/ru.json`, `resources/lang/by.json`, `resources/lang/en.json`.

## Phase 6: User Story 4 — объединить группы (Priority: P2)

**Goal**: отдельная auth-only merge page с paginated target table, search/debounce и atomic source→target merge.

**Independent Test**: authenticated source merge page excludes source, target action confirms, transfers
all distances, deletes source and returns to current group list.

### Tests for User Story 4

- [X] T036 [P] [US4] Создать unit tests для merge command, source/target validation, locks, transaction collaborator calls и no-op rejection без Eloquent в `tests/Application/Service/Group/MergeGroupsServiceTest.php`.
- [ ] T037 [P] [US4] Создать API request tests для auth, missing/equal ids, atomic distance reassignment, source deletion, target preservation и query count в `tests/Feature/Api/V1/Group/MergeGroupsActionTest.php`.
- [ ] T038 [P] [US4] Создать SPA tests для merge route guard, source exclusion, paginated target table, search debounce, confirm/cancel/pending/error/success в `resources/spa/pages/groups/MergeGroupsPage.test.ts`.

### Implementation for User Story 4

- [X] T039 [US4] Создать merge command, invariants и transactional Application service в `app/Application/Service/Group/MergeGroups.php` и `MergeGroupsService.php`; использовать Domain port и существующий transaction abstraction.
- [X] T040 [US4] Создать `MergeGroupsAction` с validated `sourceGroupId`/`targetGroupId` payload и error mapping в `app/Bridge/Laravel/Http/Controllers/Api/V1/Group/MergeGroupsAction.php`.
- [X] T041 [US4] Создать `resources/spa/pages/groups/MergeGroupsPage.vue` с source info table и impressions, переиспользованным `resources/spa/components/GroupListingTable.vue`, excluded source, зелёным target action и popup с названиями source/target.
- [X] T042 [P] [US4] Добавить merge route/action menu, API function and i18n messages в `resources/spa/router/index.ts`, `resources/spa/api/groups.ts`, `resources/spa/components/actions/` и `resources/lang/{ru,by,en}.json`.

**Checkpoint**: edit/delete/merge полностью заменяют действия старой group UI.

## Phase 7: User Story 5 — завершить миграцию и удалить legacy (Priority: P3)

**Goal**: все пользовательские входы ведут в SPA, а group-only legacy удалено после миграции shared usages.

**Independent Test**: старые GET redirects, navbar/internal links, отсутствие Blade render actions и
сохранение cube/parser consumers проверены request/regression tests.

### Tests for User Story 5

- [ ] T043 [P] [US5] Создать regression tests для navbar/internal group links, old GET redirects и отсутствия Blade content в `tests/Feature/Group/GroupNavigationTest.php` и `tests/Feature/Api/V1/SpaRoutingTest.php`.
- [ ] T044 [P] [US5] Обновить shared consumer tests для кубков, parser и distance/group operations после удаления legacy adapters в соответствующих `tests/`.

### Implementation for User Story 5

- [ ] T045 [US5] Перевести navbar и найденные внутренние group links на SPA в `resources/spa/components/navigationModels.ts`, `resources/views/layouts/`, `resources/views/` и связанных Bridge actions.
- [X] T046 [US5] Удалить group-only web GET и mutation routes в `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php` для `/groups`, `/groups/{id}` и `/groups/{id}/unit`.
- [X] T047 [US5] Мигрировать shared usages с legacy GroupsRepository/GroupsService на target Domain/Infrastructure contracts; обновить `app/Domain/Cup/`, parser и bindings.
- [X] T048 [P] [US5] Удалить заменённые `resources/views/groups/index.blade.php`, `show.blade.php`, `unit.blade.php` и group-only `app/Bridge/Laravel/Http/Controllers/Groups/*` после аудита usages.
- [X] T049 [US5] Проверить отсутствие ссылок на удалённые group-only artifacts и обновить `specs/014-group-spa-management/research.md` и `quickstart.md`.

## Phase 8: Polish & cross-cutting validation

- [ ] T050 [P] Проверить API serializer public/auth projections и все contract paths из `specs/014-group-spa-management/contracts/api.md`.
- [ ] T051 [P] Выполнить изменённые PHPUnit/Vitest tests по путям `tests/` и `resources/spa/` и исправить только относящиеся к фиче ошибки.
- [ ] T052 Выполнить финальные quality gates `composer.json`/`package.json`: `composer cs`, `composer stan`, `composer rector`, `composer test`, `npm run ci` и `git diff --check`; зафиксировать результат в `specs/014-group-spa-management/quickstart.md`.

## Dependencies & execution order

### Phase dependencies

- Setup (T001–T003) не меняет поведение и выполняется первым.
- Foundational (T004–T010) блокирует stories: audit storage, ports, serializer и event query должны быть готовы.
- US1 (T011–T019) — MVP; US2 (T020–T027) зависит от event extensions и group view foundation.
- US3 (T028–T035) зависит от Group DTO/port и authenticated route bindings.
- US4 (T036–T042) зависит от transactional group port и list UI, но может идти параллельно с US3 после foundation.
- US5 (T043–T049) выполняется после рабочих SPA paths и shared consumer migration.
- Polish (T050–T052) выполняется последним.

### Parallel opportunities

- T001–T003 можно выполнять параллельно.
- T004, T005, T007, T008 и T010 можно выполнять параллельно после аудита.
- Тесты внутри каждой user story с `[P]` независимы и могут выполняться параллельно.
- US3 и US4 могут идти параллельно после T004–T010, если изменения в общих файлах координируются.
- T043 и T044 независимы от реализации друг друга; T048 запускается только после T047.

### MVP scope

MVP — Phase 1–2 и User Story 1 (T001–T019): публичный список групп, поиск от одного символа,
pagination, count sorting, auth projection и переход в SPA. Полный релиз включает US2–US5.

### Traceability

- FR-001–FR-006 → T004–T019.
- FR-007–FR-010 → T008–T027.
- FR-011–FR-014 → T028–T035.
- FR-015–FR-016 → T036–T042.
- FR-017–FR-020 → T001–T010, T043–T052.
- SC-001–SC-004 → T011–T027.
- SC-005 → T028–T042.
- SC-006–SC-007 → T003, T009, T029, T037, T044–T052.
