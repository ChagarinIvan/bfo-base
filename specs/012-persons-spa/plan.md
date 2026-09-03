# Implementation Plan: SPA-страница персонов

**Branch**: `012-persons-spa` | **Date**: 2026-09-03 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/012-persons-spa/spec.md`

## Summary

Канонический список персонов будет реализован как маршрут `/app/persons` существующей Vue 3 SPA.
`GET /api/v1/persons` расширяется серверным поиском по имени, фильтрами разряда/года/клуба,
идентификатором клуба и сохранённой пагинацией. Таблица выносится в `PersonTable.vue` и используется
также в деталях клуба. После миграции внутренних event/cup/console-потребителей удаляются
`/persons`, старые неверсированные API-входы, Vue 2 mount списка и list-only legacy DTO/service;
legacy detail/create/edit и связанные действия сохраняются.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript, Vue 3

**Primary Dependencies**: Laravel 13, Eloquent, Vue Router 4, PrimeVue 4, Axios, Pinia, Vite

**Storage**: MySQL 8.4 через существующие `PersonRepository`/`ClubRepository` и Eloquent adapters

**Testing**: PHPUnit feature/integration tests, Vitest + Vue Test Utils, vue-tsc, ESLint/Prettier,
PHPStan, PHP CS Fixer, Rector

**Target Platform**: Laravel application behind PHP-FPM/Nginx; modern browsers; SPA assets are
built to `public/spa`

**Project Type**: Laravel web application with an incremental Vue SPA and preserved Blade legacy
flows

**Performance Goals**: Один пагинированный запрос списка без per-row HTTP/SQL-запросов; клубы для
фильтра загружаются одним запросом и кэшируются только для all-options endpoint; порядок строк детерминирован
по `lastname`, `firstname`, `id`

**Constraints**: Список содержит активных персон; фильтр клубов содержит только активные клубы;
`/persons` и `/api/person*` не зарегистрированы; `/api/v1/persons` остаётся публичным при
optional-auth policy; legacy person action routes сохраняются; page size не превышает текущий
лимит API (20 по умолчанию, максимум 100)

**Scale/Scope**: Тысячи персон и клубов; серверная фильтрация и пагинация; справочник клубов
загружается отдельным all-options endpoint без audit-полей

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. Слои**: PASS — HTTP остаётся в Bridge, criteria/use cases и DTO находятся в Application,
  запросы и Eloquent остаются в Infrastructure, UI — в `resources/spa`; новый код не добавляется
  в legacy `app/Services`.
- **II. Зависимости**: PASS — используются существующие repository interfaces и constructor
  injection; новые legacy repositories и Laravel facades не вводятся.
- **III. Тестирование**: PASS — backend API/route/integration regression tests и frontend
  unit/component tests покрывают фильтры, состояния, auth visibility, shared table и удаление
  старых входов; Application/Domain unit tests не создают Eloquent entities.
- **IV. Целевая архитектура**: PASS — внутренние потребители list-only boundary переводятся на
  Application use case с `Criteria` до удаления legacy DTO/service.
- **V. Актуальный стек**: PASS — PHP 8.5, Laravel 13, Vue 3 и текущие поддерживаемые зависимости.
- **VI. Кодстайл**: PASS — PHP imports вместо inline-FQCN; итоговые CS/Stan/Rector gates сохраняются.

Все gates проходят; неразрешённых уточнений не остаётся.

## Phase 0: Research Decisions

Подробные решения и альтернативы находятся в [research.md](research.md). Ключевые решения:

1. Сохранять один endpoint `/api/v1/persons`; фильтры нормализуются на входе и применяются одним
   query с `AND`, а поиск — `LOWER(lastname) LIKE ... OR LOWER(firstname) LIKE ...`.
2. Добавить в response только `clubId`; имя клуба разрешается на фронте через уже загруженный и
   закэшированный справочник клубов, чтобы общий компонент не делал запросов на строку.
3. Добавить отдельный V1 endpoint `/api/v1/clubs/all` с `ListAllClubService`/
   `ListAllClubAction` и `ClubRepository::all()`; он возвращает все активные `{id,name}` в
   стабильном порядке, а обычный paginated `/api/v1/clubs` остаётся без кэширования.
4. Для прежних Blade/event/cup/console потребителей создать целевой Application use case,
   принимающий `Criteria` и возвращающий нужную legacy view projection; удалить только
   list-only `LegacySearchPersonDto`, `ListLegacyPersons` и `ListLegacyPersonsService`.

## Phase 1: Design

### Backend/API

- Расширить `SearchPersonDto` полями `name`, `birthYear`, `clubId`, `rankId`; trim пустого имени,
  validation для целых идентификаторов, имени длиной 3–255 и года 1920–текущий год.
- Расширить `ListPersons`/criteria и `EloquentPersonRepository::createPaginatedQuery()` поиском
  по двум полям, годом рождения и active-club join для `clubId`. Общий список не исключает
  активную персону только из-за отсутствующего/неактивного клуба: в таком случае клубные поля
  nullable.
- Расширить `ViewPersonDto` и `PersonAssembler` данными `clubId` и
  текущим `rankId`. Сериализация audit impressions остаётся auth-only; pagination headers не
  меняются.
- Реализовать отдельный `GET /api/v1/clubs/all` через `ListAllClubService` и
  `ListAllClubAction`. В response не включать `personsCount`/audit;
  использовать `ClubRepository::all()` с `select(id,name)`, `where(active=true)` и
  `orderBy(name,id)` без искусственного лимита. Обычный `/api/v1/clubs` оставить
  пагинированным и не кэшировать его на SPA.
- Сохранить `/api/v1/persons` и `/api/v1/clubs` под `OptionalAuthenticateApiV1`; проверить, что
  новая выдача не вызывает per-row follow-up queries.

### Internal legacy migration and route cleanup

- Добавить целевой non-paginated Application use case для запросов по `Criteria` (ids/
  without-lines-and-payments) с теми ресурсами, которые нужны event/cup/console views. Перевести
  на него event/cup/prune-потребителей; для `FixYearCommand` использовать обычный
  `ListPersonsService` с `birthYear` и читать строки из возвращаемого `Slice`.
- Удалить `LegacySearchPersonDto`, `ListLegacyPersons`, `ListLegacyPersonsService`, их тест и
  только list-only API classes/routes (`ListPersonAction`, `PersonController`, collection/resource,
  `ApiRoutesServiceProvider` registration). `LegacyViewPersonDto` сохраняется для person detail,
  forms и legacy Blade projections, пока эти сценарии не мигрируют.
- Удалить `/persons` route, `ShowPersonsListAction`, `resources/views/persons/index.blade.php` и
  Vue 2 `resources/vue/components/person/Persons.vue`; убрать его component registration из
  `resources/js/app.js`, но сохранить общий legacy bootstrap/scripts, если они используются.
- Перевести navbar/listing links на `/app/persons`; missing-person/delete/payment redirects должны
  вести прямо на `/app/persons`. Не менять `/persons/{id}/show`, `/persons/create`, edit, payments,
  prompts и rank routes.

### SPA

- Добавить `PersonsPage.vue` и маршрут `/app/persons`; навигационная модель и Blade/navigation
  links должны указывать на этот URL. Nginx SPA fallback уже обслуживает `/app/*`; отдельный
  Laravel web route для `/app/persons` не добавляется.
- Вынести строки из `ClubDetailsPage.vue` в `PersonTable.vue`. Компонент принимает rows, users и
  auth state, показывает name/club/birth year/rank/actions, строит только legacy hrefs для
  person actions и SPA href для существующего клуба; null values — безопасный пустой текст.
- В `PersonsPage.vue` добавить name input с debounce 300 ms и минимумом 3 символа, rank Select,
  birth-year Select (1920..current year + no filter), active-club Select из all-options API,
  paginator и create action только для authenticated users. При изменении фильтра сбрасывать page
  в 1; значения запроса использовать camelCase.
- Использовать sequence/request guard для конкурентных запросов, отменять debounce при unmount,
  разделять loading/error/validation states и давать retry для API error. Кэш clubs/ranks по
  существующему SPA-паттерну не должен ломать работу при недоступном localStorage.
- Обновить `resources/spa/api/persons.ts`, `clubs.ts`, `api/types.ts`, i18n keys и styles. Ссылка
  имени ведёт на `/persons/{id}/show`, edit/delete остаются auth-only; create ведёт на
  `/persons/create`.

### Verification design

- PHPUnit: combined criteria, partial case-insensitive name, year validation, active/missing club,
  response shape, pagination, all-club options, public/auth serialization, old route/API 404,
  legacy route preservation, internal consumer regressions and query-count/N+1 checks.
- Vitest: API query serialization, year/filter models, pagination reset/debounce/sequence guard,
  page loading/error/empty/auth states, route/navigation destination, PersonTable rendering and
  ClubDetails reuse.
- Final feature gate: `composer test`, frontend CI/build, `composer cs`, `composer stan`,
  `composer rector -- --dry-run`, `git diff --check`, route inspection and manual quickstart.

## Project Structure

### Documentation (this feature)

```text
specs/012-persons-spa/
├── plan.md              # This file ($speckit-plan command output)
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 validation guide
├── contracts/           # Phase 1 API/UI contracts
└── tasks.md             # Phase 2 output ($speckit-tasks; not created here)
```

### Source Code (repository root)

```text
app/
├── Application/
│   ├── Dto/Person/{SearchPersonDto,ViewPersonDto,PersonAssembler}.php
│   ├── Dto/Club/{ClubOptionDto,SearchClubDto}.php                 # option DTO as needed
│   └── Service/Person/{ListPersons,FindPersons,*.php}
├── Bridge/Laravel/
│   ├── Http/Controllers/Api/V1/{Person,Club}/*Action.php
│   ├── Http/Controllers/{Event,Cup,Person,PersonPayment}/*Action.php
│   └── Provider/{ApiV1RoutesServiceProvider,WebRoutesServiceProvider}.php
├── Domain/{Person,Club,Shared}/
└── Infrastructure/Laravel/Eloquent/{Person,Club}/*Repository.php
resources/spa/
├── api/{persons,clubs,ranks,types}.ts
├── components/PersonTable.vue
├── pages/persons/{PersonsPage.vue,personsModels.ts}
├── pages/clubs/ClubDetailsPage.vue
├── router/index.ts
├── stores/auth.ts
└── i18n.ts
resources/vue/components/person/Persons.vue                         # removed
resources/views/persons/index.blade.php                              # removed
tests/
├── Feature/Api/V1/{Person,Club}/*Test.php
├── Bridge/Laravel/{Http/Controllers,Console}/**/*Person*Test.php
└── Application/Service/Person/*Test.php
resources/spa/
└── **/*.{test.ts,test.vue}
```

**Structure Decision**: Один Laravel-проект с целевыми Application/Domain/Bridge/Infrastructure
слоями и существующей Vue 3 SPA. API остаётся тонким Bridge-адаптером к use cases, Eloquent
остаётся Infrastructure, а Blade используется только для сохранённых legacy detail/form/action
потоков. Новые репозитории и новые legacy services не создаются.

## Post-Design Constitution Check

- **I–II**: PASS — all-club options и person search используют существующие interfaces/use cases;
  Domain не получает Vue/HTTP-зависимостей, фасады и новые legacy repositories отсутствуют.
- **III**: PASS — каждый новый response/route/UI behavior имеет соответствующий regression test;
  Eloquent entities создаются только в API/integration tests.
- **IV–V**: PASS — list-only boundary удаляется после миграции consumers, а реализация остаётся на
  PHP 8.5/Laravel 13/Vue 3.
- **VI**: PASS — план требует imports вместо inline-FQCN и финальный CS gate.

No constitutional violations or unresolved clarifications remain.

## Complexity Tracking

Нет нарушений конституции, требующих исключения.
