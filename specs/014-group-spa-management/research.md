# Исследование: управление группами в SPA

## Источники в репозитории

- `specs/007-club-spa-management`: принятый pattern для list/detail, `Slice`, pagination headers,
  auth-aware impressions, debounce, duplicate conflict, Vue Router и legacy redirects.
- `resources/views/groups/index.blade.php`: текущие колонки name и count distances; auth-only
  edit/delete и переход на объединение.
- `resources/views/groups/show.blade.php`: текущая таблица competition, event и competitors.
- `resources/views/groups/unit.blade.php`: текущий merge flow — source group + выбор target.
- `app/Bridge/Laravel/Http/Controllers/Groups/*`: текущие Blade actions, зависящие от legacy
  `GroupsService` и `DistanceService`.
- `ListEventsAction` + `SearchEventDto` + `EloquentEventRepository`: существующий paginated event
  read path; сейчас required `competitionId`, filtering by year and `withCount('protocolLines')`.
- `resources/spa/pages/competitions/CompetitionDetailsPage.vue` и `resources/spa/api/events.ts`:
  существующие event pagination/loading patterns.
- `GroupsRepository`/`GroupsService`: используются не только групповой страницей, но также кубками и
  parser-related code; без миграции usages удалять их опасно.

## Принятые решения

1. Группы получают отдельный public `/groups` API с `withCount('distances')`, order by count
   descending and id ascending. Это переносит count/order в SQL и исключает N+1.
2. View group использует один `events` API: `groupId` выбирает события через `distances.group_id`,
   `withCompetition=1` включает relation/`competitionName`; старые `competitionId` queries остаются
   валидными.
3. Название соревнования фильтруется в event query через relation join; если оно передано,
   требуется минимум три символа. Год и дата фильтруют дату самого старта (`events.date`)
   server-side. UI показывает только data columns, без action column.
4. Edit, delete, merge — отдельные Application use cases и authenticated API actions. Merge и delete
   transaction-safe; merge locks source/target and reassigns distances before deleting source.
5. Duplicate prevention следует club pattern: trim + case-insensitive normalized name, current id
   исключается при update, conflict is HTTP 409 with `group_name_already_exists`.
6. Старые group-only web routes и Blade actions удаляются полностью; SPA становится единственным
   пользовательским входом для групп.
7. Индекс на `distances.group_id` проверяется по существующей схеме и EXPLAIN. Если FK/index уже
   существует или `withCount` не нуждается в новом индексе, миграция не создаётся.
8. В отличие от clubs, таблица `groups` не имеет audit columns. Поэтому добавляется отдельная
   reversible migration `created_at/created_by/updated_at/updated_by` с backfill; DTO использует
   тот же `ImpressionCast`/`AuthAssembler`, что остальные aggregate models.
9. `distances.group_id` уже имеет индекс в миграции создания таблицы; добавочный индекс не нужен.

## Не выбранные альтернативы

- Полная загрузка всех групп для merge отклонена: нарушает pagination requirement и плохо масштабируется.
- Новый starts-specific endpoint отклонён: event API уже содержит пагинацию и participant count.
- Удаление `GroupsRepository` одним шагом отклонено: он нужен shared-сценариям; сначала нужен
  целевой port/adapter migration.
