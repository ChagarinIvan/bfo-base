# План реализации: управление группами в SPA

**Ветка**: `014-group-spa-management` | **Дата**: 2026-09-03 | **Спека**: [spec.md](spec.md)

## Краткое описание

Перенести публичный список и просмотр групп в Vue SPA, добавить paginated group API с сортировкой
по числу дистанций, фильтры стартов через расширение `events` API, аутентифицированные edit/delete/
merge use cases и затем удалить заменённые group-only Blade-маршруты. Подход повторяет
`007-club-spa-management`: DTO serialization groups, `Slice`, pagination headers, общие фильтры,
debounce, auth store, PrimeVue и request/frontend tests.

## Технический контекст

**Язык/версия**: PHP 8.5, TypeScript, Vue 3.

**Основные зависимости**: Laravel 13, Eloquent/MySQL 8.4, Axios, Vue Router, Pinia, PrimeVue,
Vitest/Vue Test Utils.

**Хранение**: существующие таблицы `groups`, `distances`, `events`, `competitions`,
`protocol_lines`; обязательная миграция audit columns группы, дополнительные индексы только при
доказанной необходимости для производительности.

**Тестирование**: PHPUnit/Pest-подобный проектный набор через `composer test`, frontend Vitest через
`npm run ci`, плюс узкие PHP CS/PHPStan/Rector проверки на финальном гейте.

**Платформа**: Laravel web/API и браузерный SPA под `/app`.

**Тип проекта**: монолитное Laravel web-приложение с постепенно выделяемым Vue SPA.

**Цель производительности**: один bounded API query на страницу групп и стартов без N+1; сортировка
и фильтрация выполняются в БД; debounce поиска около 300 мс.

**Ограничения**: публичное чтение, auth-only mutation и impressions; существующая API-сериализация
и pagination headers; delete/merge атомарны; не добавлять новые legacy services/repositories.

**Масштаб**: все группы и старты проекта, текущая страница выдачи — не более `perPage`; UI не
загружает весь справочник для списка или merge.

## Проверка конституции

| Принцип | Статус | Решение |
|---|---:|---|
| Application / Domain / Bridge / Infrastructure | ✅ | Use cases и DTO в Application, чистые критерии/порты в Domain, API actions и SPA bridge, Eloquent в Infrastructure. |
| Без фасадов и новых legacy-слоёв | ✅ | Зависимости через constructor injection; `app/Services` и `app/Repositories` не расширяются. |
| Unit + API/frontend tests | ✅ | Unit без Eloquent в Application/Domain; request-тесты для БД/HTTP; Vitest для debounce, filters и routes. |
| SPA-first | ✅ | Новые страницы и API — основной интерфейс; старые group-only GET удалены. |
| N+1 / quality gates | ✅ | `withCount`, SQL-фильтры, query-count tests и финальные CS/STAN/Rector/test/CI. |
| PHP 8.5 / import style | ✅ | Новые PHP-классы используют актуальный синтаксис и `use` imports. |

## Архитектура и потоки

1. `GET /api/v1/groups` принимает `name`, `page`, `perPage`; Bridge валидирует DTO, Application
   вызывает group read port, Infrastructure строит `withCount('distances')` query, serializer
   скрывает impressions для public scope.
2. `GET /api/v1/groups/{id}` возвращает один `ViewGroupDto`; SPA параллельно запрашивает
   `GET /api/v1/events?groupId={id}&withCompetition=1` для таблицы стартов.
3. `GET /api/v1/events` сохраняет текущие критерии и добавляет `groupId`, `withCompetition`,
   `competitionName`/name filter и `date`; `year` продолжает фильтровать дату события. DTO расширяется
   опциональным `competitionName` без изменения старых ответов, если флаг не указан.
4. Auth API actions вызывают update/delete/merge use cases. Update использует duplicate-preventing
   updater и возвращает 409 `group_name_already_exists`; merge выполняется в transaction с locks,
   переносит distances и удаляет source.
5. SPA router добавляет `/app/groups`, `/app/groups/:id`, `/app/groups/:id/edit`,
   `/app/groups/:id/merge`; list/detail public, edit/merge auth guard, delete через dialog.
6. После миграции web routes удаляют group-only GET, render/mutation actions и Blade views после
   аудита usages.

## Структура проекта

### Документация

```text
specs/014-group-spa-management/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/api.md
├── checklists/requirements.md
└── tasks.md
```

### Backend

```text
app/Domain/Group/
app/Application/Dto/Group/
app/Application/Service/Group/
app/Bridge/Laravel/Http/Controllers/Api/V1/Group/
app/Infrastructure/Laravel/Eloquent/Group/
database/migrations/*_add_group_impression.php
app/Bridge/Laravel/Http/Controllers/Api/V1/Event/
app/Application/Dto/Event/
app/Application/Dto/Event/SearchEventDto.php
app/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepository.php
app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php
app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php
```

### Frontend

```text
resources/spa/api/groups.ts
resources/spa/api/events.ts
resources/spa/api/types.ts
resources/spa/pages/groups/
resources/spa/components/GroupListingTable.vue
resources/spa/components/actions/
resources/spa/router/index.ts
resources/spa/components/navigationModels.ts
resources/spa/i18n.ts
resources/lang/{ru,by,en}.json
```

### Тесты и удаляемое legacy

```text
tests/Feature/Api/V1/Group/
tests/Application/Service/Group/
tests/Domain/Group/
tests/Feature/Group/
resources/spa/pages/groups/*.test.ts
resources/spa/api/groups.test.ts
resources/views/groups/{index,show,unit}.blade.php          # удалить после аудита usages
app/Bridge/Laravel/Http/Controllers/Groups/                   # удалить group-only actions
```

## Фазы реализации

### Phase 0 — исследование

Результаты зафиксированы в [research.md](research.md): изучены 007, текущие group Blade actions,
`events`/`persons` API, shared usages `GroupsRepository`/`GroupsService`, serializer и маршруты.

### Phase 1 — контракты и целевая модель

Уточнить Domain ports/criteria и DTO, API payloads, event-group query, transaction boundaries и
serialization groups. Добавить audit columns group с backfill. Проверить фактические колонки и
SQL-план сортировки `distances_count`; не добавлять индекс, если он не улучшает поддерживаемый
запрос.

### Phase 2 — вертикальные срезы

- P1: backend group list/view + events group filter, затем SPA list/detail и pagination/filter tests.
- P2: update/delete API and SPA form/dialog; duplicate handling and atomic delete tests.
- P2: merge API/use case and separate paginated merge page; source/target/error tests.
- P3: navbar/internal-link audit and deletion of obsolete artifacts.

### Phase 3 — финальная проверка

Run quickstart scenarios, changed tests, query-count/N+1 checks, `composer cs`, `composer stan`,
`composer rector` dry-run, `composer test`, `npm run ci`, `git diff --check`; inspect final legacy
usages and migration scope.

## Риски и решения

- **Shared legacy repository**: сначала инвентаризировать usages кубков/парсера; мигрировать их на
  Domain port + Infrastructure adapter before deleting old classes.
- **Inconsistent event API compatibility**: make new filters optional; keep existing competition
  filter behavior and response fields, add `competitionName` only when requested.
- **Concurrent merge/delete**: lock source/target and execute relation changes/deletion in one
  transaction; reject missing/equal targets before mutation.
- **Pagination instability**: explicit secondary id ordering and server-side count/order.
- **Index uncertainty**: compare generated SQL and EXPLAIN against existing indexes; record decision
  in implementation/plan, add migration only with measurable rationale.
- **Inconsistent historical normalization**: a follow-up migration recalculates all existing
  `normalize_name` values with `GroupNameNormalizer`, matching the current search/parser flow.

## Complexity tracking

Нет нарушений конституции и нет оправданий для новых проектов/legacy-слоёв. Расширение `events` API
выбрано вместо отдельного starts endpoint, потому что оно прямо соответствует запросу пользователя и
переиспользует существующий paginated event read path.
