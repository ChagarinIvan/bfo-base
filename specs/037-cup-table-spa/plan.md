# План реализации: таблица кубка в SPA

**Ветка**: `037-cup-table-spa` | **Дата**: 2026-09-23 | **Спека**: [spec.md](spec.md)

## Резюме

Разделить текущую SPA-страницу кубка на общий layout с карточкой и вложенные
представления «Этапы»/«Таблица». Добавить V1 query для рассчитанной таблицы группы,
чтобы frontend не загружал весь результат в память и не дублировал правила расчёта.
Таблица использует существующие PrimeVue/Listинг-паттерны, server-side pagination,
поиск от трёх символов и обязательные колонки этапов.

## Technical Context

**Frontend**: Vue 3, TypeScript, Vue Router, PrimeVue, существующие `ListingTable`,
`SlicePaginator`, `tableModels`, AbortSignal/request identity.

**Backend**: PHP 8.5/Laravel 13, V1 API в Bridge, Application query service,
Domain cup calculation, DTO assembler, pagination `Slice`.

**Existing calculation**: `CupEventsService::calculateCup()` and cup type classes
are the behavior reference. New target code must avoid adding more logic to legacy
`app/Services`; wrap/reuse calculation behind the Application use case where
needed.

**API shape**: `GET /api/v1/cups/{cupId}/tables/{groupId}`, query `name`, response
contains ordered `stages` and the full `rows` array. All query keys are camelCase.

**Performance**: calculate once per cup/group request, paginate the assembled rows
before transport serialization, and avoid one person/club query per row.

## Constitution Check

| Gate | Status | Evidence |
|---|---|---|
| Target layers | Pass with design constraint | New API query is Bridge → Application → Domain ports/assemblers; legacy calculator is adapted rather than extended with another endpoint. |
| Commands/queries | Pass | Read query receives a command/input and returns a view DTO/slice; transport DTO stays at Bridge boundary. |
| API V1 | Pass | camelCase query, DTO serializer, request test with `@see`, optional auth behavior. |
| Table rows | Complete table | The SPA receives the full calculated table for the selected group. |
| Testing | Required | API contract, application calculation mapping, SPA route/tabs/table/filter tests. |
| N+1 | Required | Stage/person/club data is assembled with batch resources or explicit eager loading. |

## Design

### SPA layout and routing

- Keep the cup card in a parent layout component.
- Use child routes analogous to `PersonLayoutPage`: default child renders the
  existing event list; `table/:groupId` renders the new table.
- Navigation buttons update Vue Router history without document reload. Group tabs
  are local router links and preserve the selected group.
- Direct old links remain outside this feature; the SPA deep-link is stable and
  can be linked from the card group badges.

### Table contract

The API returns `stages` with event/cup-event IDs, date, name and maximum points;
each row returns rank/place, person and club fields, and an ordered stage-cell map.
Each stage cell includes displayed points, whether it contributes to the total, and
protocol event/distance/line IDs for the link. `totalPoints`, `averagePoints` and
`place` are server-calculated.

The group selector is populated from the groups returned by the cup view, and every
table request includes a valid `groupId`. Name filtering follows the existing
`ListCupEventPointsService`: an empty name means no name predicate, one or two
characters are held locally/rejected, and three or more characters are matched
case-insensitively.

### Error and race handling

The table request uses AbortController plus a monotonically increasing request ID.
Only the latest group/filter request may update rows, loading or error state.
Validation for a 1–2 character name is local and no API call is made.

## Project Structure

```text
app/Application/Service/Cup/        # query command/service and DTO assembler
app/Application/Dto/Cup/            # table view DTOs/resources
app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/
app/Infrastructure/                 # repository/resource adapters if required
tests/Feature/Api/V1/Cup/            # request contract tests
tests/Application/Service/Cup/      # calculation/mapping tests
resources/spa/pages/cups/CupLayoutPage.vue
resources/spa/pages/cups/CupStagesPage.vue
resources/spa/pages/cups/CupTablePage.vue
resources/spa/pages/cups/            # view tests/models
resources/spa/api/                   # API client/types/tests
resources/spa/router/index.ts        # nested cup routes
specs/037-cup-table-spa/             # design artifacts
```

## Complexity and risks

- **Risk**: existing legacy calculator returns an in-memory array and carries
  protocol-line relations. Mitigate by isolating mapping and batch-loading related
  people/clubs; do not silently change scoring rules.
- **Risk**: dynamic stage columns can violate persisted column settings. Use a
  table-specific storage key and mark stage columns non-hideable in the component.
- **Risk**: changing `CupViewPage` can regress existing stage controls. Preserve
  current card and event tests before adding the table route.
