# Реализационный план: Управление соревнованиями в SPA

**Ветка**: `005-competition-spa-management` | **Дата**: 2026-08-30 | **Спек**: [spec.md](spec.md)

## Резюме

Расширить SPA-раздел соревнований: серверные фильтры имени и даты, просмотр соревнования с таблицей этапов, изменение и мягкое удаление, hybrid-navbar и общие UI-примитивы. Новые V1 actions принимают DTO, передают command в Application use case и возвращают assembler-produced view DTO. Для списка этапов создаётся ограниченный read-model без N+1; create и edit используют одну форму.

## Технический контекст

**Язык/версия**: PHP 8.5/Laravel 13; TypeScript 5 strict/Vue 3.

**Основные зависимости**: Sanctum, Pagerfanta/Slice, PrimeVue 4, Vue Router, Pinia, Axios, PHPUnit, Vitest. Новые пакеты не нужны.

**Хранение**: MySQL 8.4; существующие Eloquent модели Competition, Event, Flag, CupEvent, Distance и ProtocolLine. Soft delete соревнования — `active=false`.

**Тестирование**: V1 request tests, Vitest, `npm run ci`, `composer cs`, `composer stan`. Полный `composer test` — на завершении фичи или после крупного интеграционного блока.

**Платформа**: Nginx 1.28 + PHP-FPM 8.5; SPA `/app/*`, V1 `/api/v1/*`.

**Ограничения**: список соревнований остаётся постраничным; PUT/DELETE требуют Bearer token; legacy routes и Blade не меняются; этапы загружаются без запроса зависимостей на строку.

## Constitution Check

| Принцип | Статус | Решение |
|---|---|---|
| Слои Application / Domain / Bridge / Infrastructure | ✅ | V1 actions — Bridge; search/view DTO, commands и assemblers — Application; soft-delete остаётся доменным правилом. |
| Не создавать новые Services/Repositories | ✅ | `ListLegacyEventsService` сохраняет Blade read-path, а `ListEventsService` становится V1 read-path; оба используют один `ListEvents` command и `EventRepository`. |
| DI и без фасадов | ✅ | Зависимости через конструктор; facade не нужен. |
| Обязательные тесты | ✅ | Request/Vitest/N+1 регрессии планируются для каждого нового поведения. |
| Legacy coexistence | ✅ | Не меняются legacy HTTP routes; ссылки на неперенесённые функции остаются обычными legacy href. |
| Производительность | ✅ | V1 `ListEventsService` получает число участников через один `withCount` для этапов; нет N+1. |

## Phase 0: Research

См. [research.md](research.md). Решения: совместимые list-фильтры, inclusive date range, compact DTO этапов, повторное использование существующих update/disable commands, общая форма и hybrid navbar.

## Phase 1: Design

См. [data-model.md](data-model.md), [contracts](contracts/) и [quickstart.md](quickstart.md).

### Project structure

```text
specs/005-competition-spa-management/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── api-competition-management.md
│   ├── api-events.md
│   └── ui-navigation.md
└── tasks.md

app/
├── Application/
│   ├── Dto/Competition/             # query/view/assembler
│   ├── Dto/Event/                   # SearchEventDto, view DTO и EventAssembler
│   └── Service/Event/               # ListEvents command, ListLegacyEventsService и V1 ListEventsService
├── Bridge/Laravel/Http/Controllers/Api/V1/
│   ├── Competition/                 # GET one, PUT, DELETE, list filters
│   └── Event/                       # GET events?competitionId=
├── Domain/{Competition,Event}/
└── Infrastructure/Laravel/Eloquent/{Competition,Event}/

resources/spa/
├── api/
├── components/                      # action menu, confirmation, simple buttons
├── pages/competitions/
│   ├── CompetitionsPage.vue
│   ├── CompetitionDetailsPage.vue
│   ├── CompetitionForm.vue
│   ├── CreateCompetitionPage.vue
│   └── EditCompetitionPage.vue
└── router/index.ts
```

### API and UI decisions

- Extend `GET /api/v1/competitions` with `name` and `date`, preserving `year`, `page`, `perPage`, pagination headers and camelCase V1 request naming.
- Add public `GET /competitions/{id}` and `GET /events?competitionId=`; add protected `PUT` and `DELETE /competitions/{id}`.
- Expand existing `SearchCompetitionDto`, list command and repository query for filters. Existing `ViewCompetition`, `UpdateCompetitionInfo` and `DisableCompetition` commands/use cases remain the mutation path.
- Rename the existing `EventSearchDto` to `SearchEventDto` and use it in the existing `ListEvents` command. Rename the former Blade service to `ListLegacyEventsService`; `ListEventsService::execute(new ListEvents($search))` is the V1 path and returns `Slice<ViewEventDto>` through `EventRepository::paginate()` and `EventAssembler::toViewEventDto()`.
- The V1 repository query obtains the participant total with one `withCount`, not per-row queries. Flags and cups are outside this projection; `ListLegacyEventsService` and `LegacyViewEventDto` remain for Blade consumers.
- `CompetitionForm` owns shared fields and field errors; create/edit pages provide initial values and submit behaviour. Action menu and delete dialog are reusable components.
- Navbar is a single declaration of SPA routes and legacy href links, with auth-only visibility from the store.

## Post-design Constitution Check

All gates remain passed. The plan follows the 004 naming and flow: action → `SearchEventDto` + `Pagination` → existing `ListEvents` command → V1 `ListEventsService` → `EventRepository::paginate()` → `EventAssembler` → `Slice<ViewEventDto>`. `ListLegacyEventsService` isolates unchanged Blade behaviour; no repository is added.

## Complexity Tracking

No constitution violations require justification.
