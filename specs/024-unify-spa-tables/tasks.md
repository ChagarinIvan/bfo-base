# Задачи: единые SPA-таблицы и навигация результатов

**Input**: Артефакты из `specs/024-unify-spa-tables/`

## Phase 1: Подготовка

- [X] T001 Проверить все текущие `DataTable` usages и ссылки event+protocol line в `resources/spa/` и `resources/views/cup/`.

## Phase 2: Foundation — общий listing layout

- [X] T002 Написать red unit/component тесты хранилища колонок, access variants и minimum-one guard в `resources/spa/components/ListingTable.test.ts`.
- [X] T003 Реализовать стабильные column metadata, local storage sanitization и `protocolLineEventUrl` в `resources/spa/components/tableModels.ts`.
- [X] T004 Реализовать `resources/spa/components/ListingTable.vue` с горизонтальным selector-ом, scoped column slot и optional sticky filter slot.
- [X] T005 Добавить CSS-контракт selector-а, sticky filters и anchor scroll offset в `resources/spa/styles.css`.
- [X] T006 Запустить red/green тесты `ListingTable` и `tableModels`.

## Phase 3: User Story 1 — настройки и единый табличный UX (P1) 🎯 MVP

**Цель**: каждый SPA DataTable-listing получает единый layout, локальные настройки и безопасный
набор колонок для текущего access variant.

**Независимый тест**: SPA component-тесты проверяют selector, восстановление prefs, фильтры,
guest/auth columns и перевод репрезентативных листингов на `ListingTable`.

- [X] T007 [US1] Перевести `resources/spa/components/PersonTable.vue`, `resources/spa/components/GroupListingTable.vue` и `resources/spa/pages/persons/PersonsPage.vue` на `ListingTable`.
- [X] T008 [US1] Перевести `resources/spa/pages/clubs/ClubsPage.vue`, `resources/spa/pages/competitions/CompetitionsPage.vue` и `resources/spa/pages/competitions/CompetitionDetailsPage.vue` на `ListingTable`.
- [X] T009 [US1] Перевести `resources/spa/pages/groups/GroupDetailsPage.vue`, `resources/spa/pages/events/EventViewPage.vue` и `resources/spa/pages/persons/PersonViewPage.vue` на `ListingTable`.
- [X] T010 [US1] Перевести `resources/spa/pages/persons/PersonPaymentsPage.vue`, `resources/spa/pages/persons/PersonPromptsPage.vue` и таблицу history в `resources/spa/pages/persons/PersonRanksPage.vue` на `ListingTable`.
- [X] T011 [US1] Добавить/обновить SPA-тесты затронутых компонентов и страниц, включая guest/auth доступность столбцов, в `resources/spa/**/*.test.ts`.

## Phase 4: User Story 2 — sticky filters (P1)

**Цель**: фильтры каждого листинга остаются доступны при вертикальной прокрутке.

**Независимый тест**: `ListingTable`-тест закрепляет optional filter slot и CSS class; event anchor
test доказывает, что target remains scrollable.

- [X] T012 [US2] Перевести все filter-bearing list pages на `#filters` `ListingTable` slot и удалить дублирующее независимое расположение `FilterPanel` в `resources/spa/pages/**/*.vue`.
- [X] T013 [US2] Расширить `resources/spa/pages/events/EventViewPage.test.ts` и `resources/spa/components/ListingTable.test.ts` для sticky/anchor контракта.

## Phase 5: User Story 3 — канонические ссылки результата (P1)

**Цель**: переход из участия, истории разрядов и cup-результата открывает нужную строку этапа.

**Независимый тест**: URL helper и event view tests проверяют канонический hash и distance query.

- [X] T014 [US3] Заменить локальные event URL builders на `protocolLineEventUrl` в `resources/spa/pages/persons/PersonViewPage.vue` и `resources/spa/pages/persons/PersonRanksPage.vue`.
- [X] T015 [US3] Применить канонический prefixed anchor в `resources/views/cup/table.blade.php` и проверить остальные cup event links без protocol line не получают искусственный hash.
- [X] T016 [US3] Добавить regression-тесты URL в `resources/spa/components/tableModels.test.ts`, `resources/spa/pages/persons/personViewModels.test.ts` и `resources/spa/pages/persons/PersonRanksPage.test.ts`.

## Phase 6: User Story 4 — ручной пересчёт разрядов (P2)

**Цель**: аутентифицированный пользователь запускает пересчёт текущей персоны из истории разрядов.

**Независимый тест**: API request test проверяет 401/204 и пересчитанные данные; SPA test проверяет
button, pending/error и refresh истории.

- [X] T017 [US4] Написать Application unit тест command path в `tests/Application/Service/Rank/RebuildPersonRanksServiceTest.php` и API request red tests в `tests/Feature/Api/V1/Person/RebuildPersonRanksActionTest.php`.
- [X] T018 [US4] Добавить `RebuildPersonRanksAction` и protected route в `app/Bridge/Laravel/Http/Controllers/Api/V1/Person/` и `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`.
- [X] T019 [US4] Добавить API client в `resources/spa/api/persons.ts`, локализации в `resources/lang/by.json` и button/pending/error/refresh flow в `resources/spa/pages/persons/PersonRanksPage.vue`.
- [X] T020 [US4] Обновить `resources/spa/pages/persons/PersonRanksPage.test.ts` и `resources/spa/api/persons.test.ts` для auth-only rebuild flow.

## Phase 7: Полировка и гейты

- [X] T021 Запустить все затронутые frontend и PHP тесты из `specs/024-unify-spa-tables/quickstart.md`.
- [ ] T022 Запустить `npm run lint && npm run typecheck && npm run test && npm run build:spa`, `composer stan`, `composer cs`, Rector dry-run, `composer test` и `git diff --check`.
- [ ] T023 Сверить `spec.md`, `plan.md`, `tasks.md`, отметить все задачи и выполнить `$speckit-converge` для `specs/024-unify-spa-tables/`.

## Зависимости и порядок

- T001 → T002–T006 → T007–T011 → T012–T016 → T017–T020 → T021–T023.
- T007–T010 используют foundation и выполняются последовательно из-за общих component contracts.
- T014–T016 зависят только от T003; T017–T020 не зависят от frontend table migration.

## Стратегия

Сначала реализовать и протестировать безопасный shared layout, затем мигрировать все листинги,
после чего добавить links и защищённый rebuild use case. Никаких ручных проверок в задачи не
выносится: acceptance закрепляется автоматическими тестами и final gates.
