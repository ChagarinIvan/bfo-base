# Модель данных: управление клубами в SPA

**Дата**: 2026-08-31 | **Фича**: `007-club-spa-management`

## Существующие сущности

### Club

| Поле | Тип | Правило |
|---|---|---|
| id | integer | Стабильный идентификатор. |
| name | string | Обязательное после trim, максимум 255 символов. |
| normalize_name | string | Вычисляется `ClubNameNormalizer`; используется для duplicate rule, не входит в V1. |
| active | boolean | Только `true` доступен list/view/update. Удаление вне фичи. |
| created / updated | Impression | Дата-время и user id; только authenticated projection. |
| persons_count | computed integer | Количество только активных связанных персонов. |

Переходы:

```text
create valid unique name → active Club
active Club + valid unique rename → active Club with updated Impression
inactive/missing Club + update → not_found
```

`Club::updateInfo(ClubInfo, Impression)` получает уже нормализованные name values, обновляет
aggregate и записывает event обновления; отдельный `ClubUpdater` не создаётся.

### Person

| Поле/связь | Правило V1 списка клуба |
|---|---|
| id | Стабильный идентификатор и часть legacy detail URL. |
| club_id | При переданном `clubId` должен совпадать с ним; без фильтра не ограничивает выдачу. |
| firstname / lastname | Отображаемые значения; default order lastname, firstname, id. |
| birthday | Существующая nullable date; projection отдаёт только nullable `birthYear`. |
| active | Только `true`; inactive не считается и не возвращается. |
| created / updated | Только authenticated projection. |

Связь: `Club 1 ── * Person`. Person list также требует активного parent Club.

Новых таблиц, колонок и migrations нет.

## Input DTO и commands

### SearchClubDto

| Поле | Валидация | Default |
|---|---|---|
| name | nullable string; trim; пустое удаляется; 3–255 | Фильтр отсутствует. |

`ListClubs` преобразует только непустое `name` в `Criteria`. `Pagination` обрабатывается action
отдельно: `page >= 1`, `perPage` 1–100, defaults 1/20.

### SearchPersonDto

| Поле | Валидация                          | Default |
|---|------------------------------------|---|
| clubId | nullable integer, min 1, camelCase | Нет: выдаются все active persons. |

`ListPersons` передаёт `clubId`, только если он есть в paginated repository path. Без него
возвращается общий active-person list; другие legacy search fields в этот DTO не входят.

### ClubDto

```text
name: required trimmed string, max 255
```

Одинаково используется `AddClub` и `UpdateClubInfo`. Duplicate после нормализации возвращает 422
field error `name`. Update с тем же normalized name собственного клуба допустим.

### Legacy inputs

Существующие ids/year/without-lines/internal-count criteria сохраняются как
`LegacySearchClubDto`/`ListLegacyClubs` и `LegacySearchPersonDto`/`ListLegacyPersons`; они не
принимаются V1 actions. Непагинированные adapters называются `ListLegacyClubsService` и
`ListLegacyPersonsService`.

## View DTO

### ViewClubDto

```text
id: string
name: string
personsCount: integer (active persons only)
created: Impression (authenticated only)
updated: Impression (authenticated only)
```

Существующий `ViewClubDto` обновляется на месте: `normalizeName` удаляется, отдельный
`LegacyViewClubDto` не создаётся, а `ClubAssembler` сохраняет один `toViewClubDto`. Единственный
найденный legacy-потребитель производного значения, `RendersEventDistance`, строит нормализованный
индекс клубов из `name` через `NormalizedNameClubFinder::normalizeName()`.

### ViewPersonDto

```text
id: string
lastname: string
firstname: string
birthYear: integer|null
created: Impression (authenticated only)
updated: Impression (authenticated only)
```

`birthday`, citizenship, clubId, eventsCount, payments и protocol lines отсутствуют.
`LegacyViewPersonDto` сохраняет полный старый contract для Blade, console и legacy APIs;
`PersonAssembler` предоставляет `toViewPersonDto` и `toLegacyViewPersonDto`.

### Impression

```text
at: ISO-8601 datetime
by: string user id
```

Serializer groups полностью исключают impression properties из public JSON. В UI календарная
часть отображается как `YYYY-MM-DD`; birth year — четыре цифры.

## Repository read paths

### ClubRepository::paginate

- `club.active=true`;
- optional `LOWER(name) LIKE %lower(name)%`;
- constrained count `persons.active=true`;
- order `club.name ASC, club.id ASC`;
- `Slice<Club>` через `EloquentQueryAdapter`.

Legacy `byCriteria()` и `oneByCriteria()` сохраняются для ids, normalized lookup и старых callers.

### PersonRepository::paginate

- optional `person.club_id=clubId`;
- `person.active=true`; active parent Club требуется, только когда передан `clubId`;
- order `person.lastname ASC, person.firstname ASC, person.id ASC`;
- без eager load payments, protocol lines и других rich relations;
- `Slice<Person>` через `EloquentQueryAdapter`.

Legacy `byCriteria()` сохраняет прежние выборки для команд и старого API.

## SPA state

- **ClubFilters**: pending name, applied name, pagination, field errors, latest request id.
- **ClubListState**: clubs, users for authenticated impressions, loading/error/empty.
- **ClubDetailsState**: club, paginated persons, users, independent person pagination,
  loading/not-found/error.
- **ClubFormState**: name, field errors, submit pending/error; общий для create/edit.
- **NavigationState**: public list/detail, guarded create/edit, legacy person href.

Reusable helpers сохраняют minimum 3, debounce 300 ms, trim, page reset, pagination headers и 422
field mapping одинаковыми для competitions и clubs.
