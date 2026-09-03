# Tasks: Перевод соревнований на SPA

**Input**: Design documents from `/specs/011-competition-links-to-spa/`

**Prerequisites**: `spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/ui-navigation.md`, `quickstart.md`

## Phase 1: Setup

- [X] T001 Провести inventory всех competition list/view/create/edit ссылок в `app/`, `resources/`, `tests/` и документации
- [X] T002 [P] Зафиксировать текущие SPA routes и API endpoints в `resources/spa/router/index.ts` и `resources/spa/api/competitions.ts`

## Phase 2: Foundational

- [X] T003 Составить таблицу для каждого legacy action/view/route: удалить, redirect или сохранить transition в `specs/011-competition-links-to-spa/research.md`
- [X] T004 [P] Проверить coverage существующих SPA router и V1 API тестов перед удалением legacy в `resources/spa/router/index.test.ts` и `tests/Feature/Api/V1/Competition/`
- [X] T005 Добавить regression-проверки canonical competition URLs и запрет рендера legacy views в `tests/Feature/Competition/CompetitionNavigationTest.php`

## Phase 3: User Story 1 — Единая навигация соревнований (P1) 🎯 MVP

**Independent test**: navbar, root, 404, login и registration ведут на `/app/competitions`; в коде нет рабочих внутренних ссылок на legacy competition list.

- [X] T006 [P] [US1] Перевести пункт соревнований navbar на `/app/competitions` в `resources/views/layouts/navbar.blade.php`
- [X] T007 [P] [US1] Перевести root redirect на SPA list в `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`
- [X] T008 [P] [US1] Перевести competition fallback link 404 на SPA в `resources/views/errors/404error.blade.php`
- [X] T009 [P] [US1] Перевести login и registration completion redirects на SPA list в `app/Bridge/Laravel/Http/Controllers/Login/SignInAction.php` и `app/Bridge/Laravel/Http/Controllers/Registration/SendRegistrationDataAction.php`
- [X] T010 [US1] Обновить PHPUnit проверки navbar/root/404/login/registration и запустить targeted route tests в `tests/Bridge/Laravel/Http/Controllers/`

## Phase 4: User Story 2 — SPA просмотр и управление (P1)

**Independent test**: list → details → create/edit проходит только по `/app/competitions*`; create/edit защищены SPA login guard; event/protocol links остаются рабочими.

- [X] T011 [P] [US2] Перевести list/detail/create/edit links и action controls на canonical SPA paths в `resources/spa/pages/competitions/CompetitionsPage.vue`, `CompetitionDetailsPage.vue`, `CreateCompetitionPage.vue` и `EditCompetitionPage.vue`
- [X] T012 [P] [US2] Добавить/обновить Vitest проверки переходов list/detail/create/edit и auth redirect в `resources/spa/router/index.test.ts`
- [X] T013 [US2] Проверить post-save и post-delete navigation, обновив `resources/spa/pages/competitions/` и `resources/spa/api/competitions.ts` только при необходимости
- [X] T014 [US2] Добавить request/UI regression tests для SPA competition navigation и сохранения неперенесённых event/protocol links в `tests/Feature/Api/V1/Competition/` и `tests/Bridge/Laravel/Http/Controllers/`

## Phase 5: User Story 3 — Удаление покрытого legacy (P2)

**Independent test**: после inventory удалённые competition Blade entry points не имеют usages, а retained redirects (если нужны) не рендерят старые views.

- [X] T015 [US3] Удалить usages `ShowCompetitionsListAction` из `app/`, `resources/` и `tests/`, кроме явно документированных transition redirects
- [X] T016 [US3] Удалить полностью заменённые web routes из `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`
- [X] T017 [US3] Удалить заменённые actions и Blade views `app/Bridge/Laravel/Http/Controllers/Competition/ShowCompetitionsListAction.php`, `ShowCompetitionAction.php`, `ShowCreateCompetitionFormAction.php`, `ShowEditCompetitionFormAction.php` и `resources/views/competitions/`
- [X] T018 [US3] Удалить или обновить legacy controller tests в `tests/Bridge/Laravel/Http/Controllers/Competition/` согласно inventory, сохранив тесты retained behavior
- [X] T019 [US3] Удалить legacy competition store/update/delete actions, web routes и их тесты после подтверждения эквивалентных V1 API endpoints

## Phase 6: Polish & Cross-Cutting

- [X] T020 [P] Обновить navigation contract и legacy inventory результатами реализации в `specs/011-competition-links-to-spa/contracts/ui-navigation.md` и `specs/011-competition-links-to-spa/research.md`
- [X] T021 [P] Выполнить repository search на legacy competition URLs/actions и проверить отсутствие новых N+1
- [X] T022 Запустить `composer cs`, `composer stan`, `composer rector`, backend tests и `npm run ci`
- [X] T023 Выполнить manual quickstart из `specs/011-competition-links-to-spa/quickstart.md`

## Dependencies & Execution Order

- Setup T001–T002 → Foundational T003–T005 → User Stories.
- US1 и US2 зависят от inventory и могут частично выполняться параллельно после T005.
- US3 начинается после US1/US2, чтобы не удалить ещё используемые entry points.
- Polish выполняется после всех выбранных story tasks.

## Parallel opportunities

- T006–T009 затрагивают разные файлы и могут выполняться параллельно.
- T011–T012 могут выполняться параллельно при согласованном контракте.
- T020–T021 независимы после завершения миграции.

## Implementation strategy

MVP — User Story 1: все основные входы ведут в SPA. Затем закрыть list/view/create/edit flow
(US2), и только после inventory удалить покрытый legacy (US3). В конце выполнить полный quality
gate и manual quickstart.

## Validation notes

- `composer cs`, `composer stan`, `composer rector`, `npm run ci`, PHP syntax checks and the
  navigation regression test passed.
- `composer test` was executed against MySQL and a temporary SQLite database. Both environments
  are unavailable/incompatible with the repository migration setup (MySQL connection/schema
  unavailable; SQLite rejects legacy `ALTER TABLE ... ADD PRIMARY KEY`). This is an environment
  blocker, not a feature assertion failure.
- Manual HTTP probing was limited to `php artisan route:list` because the sandbox cannot keep the
  local server socket available; the route inventory confirms legacy GET pages are absent and V1
  plus transition mutation routes remain.
