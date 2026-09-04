# План реализации: SPA-управление персональными промптами

**Ветка**: `015-persons-spa-management` | **Дата**: 2026-09-04 | **Спека**: [spec.md](spec.md)

## Краткое описание

Перенести просмотр и CRUD персональных промптов в Vue SPA с paginated API, auth-aware действиями,
общей формой и подтверждением удаления; ссылку со страницы персоны направить в SPA. Удалить только
prompt-only Blade routes/actions/views после usage audit и скрыть колонку клуба в таблице участников.
Shared parser/rank/import consumers сохранить.

## Технический контекст

**Язык/версия**: PHP 8.5, TypeScript, Vue 3

**Зависимости**: Laravel 13, Eloquent/MySQL 8.4, Axios, Vue Router, Pinia, PrimeVue, Vitest/Vue Test Utils

**Хранение**: существующие таблицы `person`, `persons_prompt`; миграция не предполагается

**Тестирование**: PHPUnit через `composer test`, API/request tests, frontend Vitest; финально CS/STAN/Rector/CI

**Платформа**: Laravel web/API и Vue SPA под `/app`

**Performance**: один bounded query на страницу промптов без N+1; stale list responses не применяются

**Ограничения**: auth-only read и mutations, текущие validation/metaphone rules, без новых legacy services/repositories

**Масштаб**: выдача ограничена `perPage`; CRUD только для prompt конкретной персоны

## Проверка конституции

| Принцип | Статус | Решение |
|---|---:|---|
| Application / Domain / Bridge / Infrastructure | ✅ | API actions, Application commands/services, существующие Domain rules, Eloquent Infrastructure. |
| Без фасадов и legacy-расширения | ✅ | Constructor injection; `app/Services` не расширяется, shared legacy удаляется только после аудита. |
| Unit + API/frontend tests | ✅ | Unit без Eloquent; request tests для БД/HTTP; Vitest для routes, states и actions. |
| SPA-first | ✅ | Новый prompt путь — SPA; старые web routes удаляются без redirect/stub. |
| N+1 / quality gates | ✅ | Pagination и ограниченная выдача; финальные CS/STAN/Rector/test/CI. |
| PHP 8.5 / imports | ✅ | Актуальный синтаксис и `use` imports. |

## Архитектура и потоки

1. SPA route `/app/persons/:personId/prompts` запрашивает paginated `GET /api/v1/person-prompts`;
   API Bridge собирает criteria под auth middleware, Application вызывает prompt read service, Infrastructure возвращает
   ограниченную выдачу и pagination headers.
2. Create/update/delete API actions создают commands и вызывают существующие Application services;
   auth middleware и domain factory/updater сохраняют текущие правила и metaphone.
3. SPA добавляет list/form pages, API client/types и auth-aware actions; list использует request-id
   guard, paginator и состояния loading/error/empty.
4. `persons/show.blade.php` получает ссылку на SPA; prompt-only web routes/actions/views и legacy
   `PersonPromptService` удаляются после полного usage audit.
5. `ClubDetailsPage` передаёт контекст скрытия club column в `PersonTable`; глобальная таблица персон
   не меняется.

## Структура проекта

```text
app/Bridge/Laravel/Http/Controllers/Api/V1/PersonPrompt/
app/Application/Service/PersonPrompt/  app/Application/Dto/PersonPrompt/
app/Domain/PersonPrompt/  app/Infrastructure/Laravel/Eloquent/PersonPrompt/
app/Bridge/Laravel/Provider/{ApiV1RoutesServiceProvider,WebRoutesServiceProvider}.php
resources/spa/api/{personPrompts,types}.ts  resources/spa/pages/personPrompts/
resources/spa/router/index.ts  resources/spa/components/PersonTable.vue
tests/Feature/Api/V1/PersonPrompt/  tests/Application/Service/PersonPrompt/
resources/spa/pages/personPrompts/*.test.ts
```

**Решение по структуре**: использовать существующие целевые слои и SPA-паттерны фич 004/007/011/014;
новые prompt API/read contracts не помещать в legacy `app/Services`.

## Фазы реализации

### Phase 0 — исследование

Результаты зафиксированы в [research.md](research.md): проверены PersonPrompt domain/Application,
serializer/pagination, маршруты, SPA API/pages, club PersonTable и shared usages.

### Phase 1 — контракты и подготовка

Добавить/уточнить paginated criteria/API DTO и bindings, покрыть API contract tests; определить точные
legacy files по usage audit и добавить миграцию `active` для старых prompt-записей.

### Phase 2 — вертикальные срезы

- P1: authenticated list API и SPA list с pagination, states и link из person detail.
- P1: create/edit API и общая SPA form с validation/auth/loading behavior.
- P2: delete API, confirmation и refresh/last-page behavior.
- P2: убрать club column, удалить prompt-only legacy routes/actions/views и проверить shared consumers.

### Phase 3 — финальная проверка

Запустить quickstart, изменённые PHP/Vitest tests, route/usages audit, query/N+1 checks и полный DoD.

## Риски и решения

- **Shared legacy usages**: удалять только prompt-only artifacts; parser/import/rank handlers остаются.
- **API naming compatibility**: повторить существующие plural/resource и pagination conventions после проверки соседних API actions.
- **Pagination race**: request sequence guard и explicit stable id ordering.
- **Auth projection**: public serializer не раскрывает audit details; mutation middleware единообразен.

## Complexity Tracking

Нет нарушений конституции и нет оправданий для новых legacy-слоёв или репозиториев.
