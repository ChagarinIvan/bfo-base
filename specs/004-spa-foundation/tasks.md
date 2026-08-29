---

description: "Task list for the SPA foundation feature"
---

# Tasks: Фронтенд-фундамент — постепенный переход на SPA

**Input**: Design documents from `/specs/004-spa-foundation/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/`, `quickstart.md`

**Tests**: Интеграционные PHPUnit-тесты и frontend quality-gate обязательны согласно
`FR-024`, `FR-025`, конституции и критериям успеха спецификации.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: задача может выполняться параллельно с другими задачами при выполненных зависимостях
- **[Story]**: user story, к которой относится задача
- Каждая задача содержит конкретный путь изменяемого или создаваемого файла

## Path Conventions

- Backend: `app/`, `config/`, `database/`, `routes/`, `tests/`
- Frontend: `resources/spa/`, `vite.config.ts`, `tsconfig.json`, `package.json`
- Deployment/documentation: `enviroment/nginx/`, `FRONTEND.md`, `DEPLOYMENT.md`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Подготовить зависимости, каталоги и конфигурацию независимой SPA-сборки.

- [X] T001 [P] Добавить Vue 3, Vite, TypeScript, Vue Router, Pinia, Axios, PrimeVue 4 и PrimeIcons в `package.json` и обновить `package-lock.json`
- [X] T002 [P] Создать базовую конфигурацию строгого TypeScript для SPA в `tsconfig.json`
- [X] T003 [P] Создать конфигурацию Vite с root `resources/spa`, base `/spa/`, output `public/spa/` и отдельным entrypoint в `vite.config.ts`
- [X] T004 [P] Добавить frontend-скрипты `dev:spa`, `build:spa`, `lint`, `typecheck`, `test` и `ci` в `package.json`
- [X] T005 Создать SPA entry HTML с mount point для Vue-приложения в `resources/spa/index.html`
- [X] T006 Создать точку входа Vue-приложения и подключить Pinia, Vue Router и PrimeVue Aura в `resources/spa/main.ts`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Реализовать общую API, auth, envelope, инфраструктуру маршрутов и базовые frontend-примитивы.

**⚠️ CRITICAL**: User stories нельзя считать завершёнными до окончания этой фазы.

Dedicated security-test tasks (CORS, password leakage, SQL injection, mass assignment) отложены
по решению пользователя; функциональные требования и runtime quality gates остаются в scope.

- [X] T007 [P] Настроить серверный срок действия Sanctum-токенов `expiration = 1440` в `config/sanctum.php`
- [X] T008 [P] Создать Infrastructure auth adapter `SanctumUser` с `HasApiTokens` в `app/Infrastructure/Sanctum/SanctumUser.php`, не добавляя Sanctum-зависимость в `app/Domain/Auth/User.php`
- [X] T009 Создать миграцию таблицы `personal_access_tokens` Sanctum в `database/migrations/2026_08_29_000000_create_personal_access_tokens_table.php`
- [ ] T010 [P] Создать базовый V1 API Resource с single-resource envelope `{data: ...}` в `app/Bridge/Laravel/Http/Resources/Api/V1/AbstractV1Resource.php`
- [ ] T011 [P] Создать базовый V1 Resource Collection с `data`, `meta.pagination` и `links` в `app/Bridge/Laravel/Http/Resources/Api/V1/AbstractV1Collection.php`
- [X] T012 [P] Настроить явный error envelope для validation/auth ошибок `{errors: [...]}` в API action adapter и V1 controllers; application-ошибки преобразуются автоматически через `#[HttpError]` в `app/Bridge/Laravel/Http/Serialization/ApiErrorResponse.php`; не использовать `app/Bridge/Laravel/Exceptions/Handler.php`
- [X] T013 Создать выделенный провайдер маршрутов `/api/v1/*` без изменения `app/Bridge/Laravel/Provider/ApiRoutesServiceProvider.php` в `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- [X] T014 Настроить auth provider на `App\Infrastructure\Sanctum\SanctumUser` в `config/auth.php` и зарегистрировать `ApiV1RoutesServiceProvider` в фактическом provider registry `config/app.php`
- [ ] T015 [P] Создать общие API-типы envelope, ошибок, pagination и auth в `resources/spa/api/types.ts`
- [ ] T016 [P] Создать центральный Axios-клиент с Bearer-interceptor, обработкой 401 и редиректом на `/app/login` в `resources/spa/api/client.ts`
- [ ] T017 Создать Pinia auth store с token/user state, `localStorage`, `login()`, `logout()` и очисткой состояния при 401 в `resources/spa/stores/auth.ts`
- [X] T018 Создать корневой компонент SPA с `RouterView`, layout и PrimeVue Toast в `resources/spa/App.vue`
- [X] T019 Создать базовый layout SPA с навигацией и контейнером уведомлений в `resources/spa/components/AppLayout.vue`
- [X] T020 Создать Vue Router с `/app/*`, публичным fallback и navigation guard для `meta.requiresAuth` в `resources/spa/router/index.ts`
- [X] T021 Настроить Nginx fallback для `/app` и `/app/*` на `public/spa/index.html` в `enviroment/nginx/conf.d/app.conf.example`
- [ ] T022 [P] Создать PHPUnit-базовый класс API V1 с `RefreshDatabase`, helper для создания и установки Sanctum Bearer-токена в `Authorization` header и проверками envelope в `tests/Feature/Api/V1/ApiV1TestCase.php`
- [X] T023 [P] Добавить frontend ESLint 9 flat config с Vue/TypeScript правилами, запретом `any`, `console.log` и `v-html` в `eslint.config.mjs`
- [X] T024 [P] Добавить Prettier-конфигурацию frontend-кода в `.prettierrc.js`
- [X] T024a [P] Ограничить CORS разрешёнными origin через `CORS_ALLOWED_ORIGINS`, не используя wildcard в production, в `config/cors.php`

**Checkpoint**: миграции и конфигурация применяются, Vite собирает пустой SPA в `public/spa/`,
а `/app/*` получает shell без изменения legacy Blade-маршрутов.

---

## Phase 3: User Story 1 — Посетитель просматривает список соревнований (Priority: P1) 🎯 MVP

**Goal**: Анонимный посетитель видит текущий список соревнований, меняет фильтр года и получает понятную ошибку API.

**Independent Test**: Открыть `/app/competitions` без токена, проверить список, сменить год,
проверить envelope и обновить страницу на `/app/competitions` без 404.

### Tests for User Story 1

- [ ] T025 [P] [US1] Добавить request-тесты публичного списка, year-фильтра, pagination, envelope и анонимного набора полей в `tests/Feature/Api/V1/Competition/ListCompetitionsActionTest.php`
- [ ] T026 [P] [US1] Добавить frontend-тест загрузки списка, смены года и отображения API-ошибки в `resources/spa/pages/competitions/CompetitionsPage.test.ts`

### Implementation for User Story 1

- [X] T027 [P] [US1] Добавить группы сериализации DTO: публичные поля и условные `created`/`updated` при валидном Bearer в `app/Application/Dto/Serialization/Groups.php` и `app/Bridge/Laravel/Http/Serialization/ApiDtoSerializer.php`
- [ ] T028 [P] [US1] Создать V1 `CompetitionCollection` с pagination metadata и links в `app/Bridge/Laravel/Http/Resources/Api/V1/Competition/CompetitionCollection.php`
- [ ] T029 [US1] Реализовать публичный `ListCompetitionsAction` с year-фильтром и `LengthAwarePaginator` через существующий Application service в `app/Bridge/Laravel/Http/Controllers/Api/V1/Competition/ListCompetitionsAction.php`
- [X] T030 [US1] Зарегистрировать `GET /api/v1/competitions` в `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- [ ] T031 [US1] Создать типы `Competition`, `CompetitionCollectionResponse` и query-параметров в `resources/spa/api/types.ts`
- [ ] T032 [US1] Реализовать страницу DataTable с колонками соревнования, loading/error/empty states и year-фильтром в `resources/spa/pages/competitions/CompetitionsPage.vue`
- [ ] T033 [US1] Зарегистрировать маршруты `/app/competitions` и layout integration в `resources/spa/router/index.ts`
- [ ] T034 [US1] Подключить страницу списка к центральному API-клиенту и отформатировать даты в `resources/spa/pages/competitions/CompetitionsPage.vue`

**Checkpoint**: US1 полностью работает без аутентификации и может быть продемонстрирована отдельно.

---

## Phase 4: User Story 2 — Аутентифицированный пользователь создаёт соревнование (Priority: P2)

**Goal**: Авторизованный пользователь создаёт соревнование через SPA, получает inline validation errors
и возвращается в список; истёкший токен приводит к login-flow.

**Independent Test**: С валидным Sanctum Bearer вызвать `POST /api/v1/competitions`, проверить 201 single-resource
envelope, валидацию 422 и отказ без токена. Полный SPA-тест требует завершённого US3.

### Tests for User Story 2

- [ ] T035 [P] [US2] Добавить request-тесты создания с валидным токеном, отсутствующим токеном, отсутствующими полями и правилом `to >= from` в `tests/Feature/Api/V1/Competition/CreateCompetitionActionTest.php`
- [ ] T036 [P] [US2] Добавить frontend-тест формы, inline validation errors, success notification, redirect и expired-token redirect в `resources/spa/pages/competitions/CreateCompetitionPage.test.ts`

### Implementation for User Story 2

- [ ] T037 [P] [US2] Создать типы `CreateCompetitionRequest`, `CompetitionResourceResponse` и validation errors в `resources/spa/api/types.ts`
- [X] T038 [US2] Реализовать `CreateCompetitionAction` с server-side validation, `UserId` из `$request->user()->id` и вызовом `AddCompetitionService` в `app/Bridge/Laravel/Http/Controllers/Api/V1/Competition/CreateCompetitionAction.php`
- [X] T039 [US2] Зарегистрировать защищённый `POST /api/v1/competitions` с `auth:sanctum` в `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- [ ] T040 [US2] Реализовать форму PrimeVue с `name`, `description`, `from`, `to`, `mass`, client-side UX validation и inline server errors в `resources/spa/pages/competitions/CreateCompetitionPage.vue`
- [ ] T041 [US2] Добавить ссылку «Создать соревнование», protected route и redirect после успешного создания в `resources/spa/components/AppLayout.vue` и `resources/spa/router/index.ts`
- [ ] T042 [US2] Добавить обновление списка после успешного создания без полной перезагрузки в `resources/spa/pages/competitions/CreateCompetitionPage.vue`

**Checkpoint**: API создания готов и покрыт тестами; после US3 полный пользовательский путь SPA выполняется независимо от US1.

---

## Phase 5: User Story 3 — Пользователь входит в SPA (Priority: P3)

**Goal**: Пользователь входит, токен автоматически используется в API, logout отзывает его,
а новая вкладка восстанавливает auth state из `localStorage`.

**Independent Test**: Выполнить login → me → logout через API и убедиться, что отозванный/просроченный
токен возвращает 401; проверить SPA login/logout и новую вкладку.

### Tests for User Story 3

- [ ] T043 [P] [US3] Добавить интеграционные тесты login в `tests/Feature/Api/V1/Auth/LoginActionTest.php`, logout в `tests/Feature/Api/V1/Auth/LogoutActionTest.php` и users в `tests/Feature/Api/V1/Auth/ListUsersActionTest.php`; отдельно покрыть expired token, 401 envelope и rate limit
- [ ] T044 [P] [US3] Добавить frontend-тесты auth store для login/logout, localStorage, Bearer header attachment, 401 cleanup и восстановления между вкладками в `resources/spa/stores/auth.test.ts`
- [ ] T045 [P] [US3] Добавить тест navigation guard для redirect unauthenticated user на `/app/login` в `resources/spa/router/index.test.ts`

### Implementation for User Story 3

- [X] T046 [P] [US3] Создать `LoginResponseDto` в Application с token и Bearer token type в `app/Application/Dto/Auth/LoginResponseDto.php`
- [X] T047 [P] [US3] Создать `UserDto` без password и внутренних полей в `app/Application/Dto/Auth/UserDto.php`
- [ ] T048 [US3] Реализовать `LoginService` с email/password validation, rate limit, credentials check и Sanctum token creation через Infrastructure adapter в `app/Application/Service/Auth/LoginService.php` и `app/Infrastructure/Sanctum/SanctumLoginAuthenticator.php`
- [X] T049 [P] [US3] Реализовать `LogoutAction` с отзывом только current access token для DELETE route в `app/Bridge/Laravel/Http/Controllers/Api/V1/Auth/LogoutAction.php`
- [X] T050 [P] [US3] Реализовать приватный `ListUsersAction` через `ListUsersService`, возвращающий полный список `UserDto[]` без входного DTO, фильтров и пагинации в `app/Bridge/Laravel/Http/Controllers/Api/V1/Auth/ListUsersAction.php`
- [X] T051 [US3] Зарегистрировать login/logout/users routes и `auth:sanctum` middleware в `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- [ ] T052 [US3] Реализовать страницу login с return URL, invalid credentials message и redirect в `resources/spa/pages/auth/LoginPage.vue`
- [X] T053 [US3] Подключить auth actions к API-клиенту; auth store хранит token без отдельного endpoint профиля в `resources/spa/stores/auth.ts`
- [ ] T054 [US3] Добавить login route, public/private route metadata и logout navigation в `resources/spa/router/index.ts` и `resources/spa/components/AppLayout.vue`

**Checkpoint**: US3 завершает зависимость US2; login, logout, token TTL 1440 минут и повторный login после 401 работают.

---

## Phase 6: User Story 4 — Разработчик добавляет новую страницу в SPA (Priority: P4)

**Goal**: Разработчик может добавить `.vue`-страницу и маршрут по инструкции, не затрагивая Blade-сайт.

**Independent Test**: Создать тестовую страницу `/app/hello` по `FRONTEND.md`, открыть её напрямую и обновить;
проверить, что `/groups` продолжает работать.

- [ ] T055 [P] [US4] Создать frontend developer guide со структурой каталогов, Composition API, Pinia, Router, API client и командами в `FRONTEND.md`
- [ ] T056 [P] [US4] Создать deployment guide со сборкой SPA, Nginx `/app/*`, Sanctum TTL и coexistence с Blade в `DEPLOYMENT.md`
- [ ] T057 [US4] Добавить в `FRONTEND.md` пошаговый пример новой страницы и маршрута `/app/hello` с точными путями `resources/spa/pages/` и `resources/spa/router/index.ts`
- [ ] T058 [US4] Проверить и при необходимости скорректировать Nginx-конфигурацию так, чтобы `/app/*` и legacy `/groups` обслуживались раздельно в `enviroment/nginx/conf.d/app.conf.example`
- [ ] T059 [US4] Добавить smoke-тест маршрутов SPA shell и legacy Blade в `tests/Feature/Api/V1/SpaRoutingTest.php`

**Checkpoint**: новый разработчик может добавить страницу только по руководству; SPA и Blade работают одновременно.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Проверить качество, безопасность, производительность и полный quickstart перед передачей фичи.

- [ ] T060 [P] Проверить отсутствие новых N+1 и неограниченных выборок в V1 list implementation в `tests/Feature/Api/V1/Competition/ListCompetitionsTest.php`
- [X] T061 [P] Обновить существующие Docker/frontend build steps для сборки `public/spa/` без поломки laravel-mix в `Dockerfile`
- [ ] T062 Запустить `npm run lint`, `npm run typecheck`, `npm run test` и `npm run build:spa`, исправить ошибки в соответствующих файлах `resources/spa/`, `package.json` и конфигурации frontend
- [ ] T063 Запустить `composer cs`, `composer stan`, `composer rector` и `composer test`, исправить только относящиеся к фиче проблемы в изменённых PHP-файлах
- [ ] T064 Выполнить все сценарии из `specs/004-spa-foundation/quickstart.md` и зафиксировать результат проверки в `specs/004-spa-foundation/quickstart.md`
- [ ] T065 Проверить `git diff` на отсутствие изменений legacy API provider, Blade routes/templates и секретов перед PR в `specs/004-spa-foundation/plan.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: нет зависимостей; T001–T006 подготавливают frontend-среду.
- **Foundational (Phase 2)**: зависит от Setup и блокирует все user stories.
- **US1 (P1)**: зависит от T010–T016, T020–T024; независим от auth endpoints.
- **US2 (P2)**: API-часть зависит от Foundational; полный SPA acceptance зависит от US3 (T048–T054), потому что форма требует login и protected route.
- **US3 (P3)**: зависит от общей Sanctum/API-инфраструктуры; после завершения разблокирует полный сценарий US2.
- **US4 (P4)**: зависит от собранного SPA и Nginx из Foundational; рекомендуется выполнять после US1–US3 для проверки общего фундамента.
- **Polish**: зависит от всех user stories, входящих в MVP/release scope.

### User Story Completion Order

1. **US1 P1** — первый независимый вертикальный срез и MVP.
2. **US3 P3 auth foundation** — технически необходим для запуска полного US2, хотя в spec имеет приоритет P3.
3. **US2 P2** — завершить после US3 и проверить сквозной protected flow.
4. **US4 P4** — закрепить повторяемый developer workflow.

### Parallel Opportunities

- После T001–T005 можно параллельно выполнять T002, T003, T004 и T005.
- После T009 можно параллельно создавать envelope resources (T010–T011), error handling (T012), API test base (T022) и frontend tooling (T023–T024).
- В US1 backend resource (T027–T028), API test (T025) и frontend test (T026) могут стартовать параллельно; action/route зависят от контрактов и базовых классов.
- В US2 тесты T035–T036 и типы T037 могут выполняться параллельно; backend action и frontend form затрагивают разные файлы.
- В US3 T046–T047, T049–T050 и frontend tests T044–T045 могут выполняться параллельно после T022; login route registration T051 зависит от actions.
- В US4 документация T055–T057 и smoke-test T059 могут выполняться параллельно; Nginx проверка T058 зависит от актуального deployment guide.
- В Polish T060–T061 независимы друг от друга; T062–T064 выполняются после завершения реализации.

---

## Parallel Example: MVP (User Story 1)

```text
После завершения Phase 2:

Task T025: PHPUnit contract/integration tests for public competitions API
Task T026: frontend tests for list/filter/error states
Task T027: DTO serialization groups
Task T028: CompetitionCollection
Task T031: frontend API types
```

Затем последовательно выполнить T029 → T030 и T032 → T033 → T034, после чего прогнать
критерии US1 из quickstart.

## Parallel Example: Auth + Creation

```text
После Phase 2:

Task T043: auth API tests
Task T044: auth store tests
Task T046: LoginResponseDto
Task T047: AuthUserDto
Task T035: competition creation API tests
Task T037: creation API types
```

После реализации T048–T054 выполнить T038–T042 и проверить US2 end-to-end.

---

## Implementation Strategy

### MVP First

1. Завершить Phase 1 и Phase 2.
2. Выполнить US1 — публичный список соревнований.
3. Остановиться и проверить US1 через браузер, API и quickstart.
4. Затем реализовать US3 auth foundation и US2 protected creation.

### Incremental Delivery

1. Phase 1 + Phase 2 → пустой, собираемый SPA и стабильная V1 API-инфраструктура.
2. US1 → публичная страница списка, первый демонстрируемый MVP.
3. US3 → login/logout/token lifecycle.
4. US2 → создание соревнования и полный защищённый путь.
5. US4 → документация для последующих миграций.
6. Phase 7 → quality gates, regression checks и quickstart validation.

### Notes

- Все задачи используют обязательный формат checkbox + ID; story-фазы имеют `[USn]`.
- `[P]` отмечает только задачи без зависимости от незавершённой задачи и без конфликта по одному файлу.
- Новые Services/Repositories не создавать; использовать существующие Application services.
- Legacy API provider, Blade routes/templates и Domain/Application contracts не расширять без отдельного решения.
