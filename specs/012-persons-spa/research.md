# Research: SPA-страница персонов

## Decision 1: Расширить существующий V1 persons list

**Decision**: Оставить `GET /api/v1/persons` единственным HTTP-источником списка. Добавить
camelCase критерии `name`, `birthYear`, `clubId`, `rankId`, сохранив `page`, `perPage` и
`X-Pagination-*` headers.

**Rationale**: V1 уже использует целевой `ListPersons`/`PersonRepository`, optional-auth
middleware и материализованный current rank. Расширение одного контракта не создаёт конкурирующий
API и позволяет SPA фильтровать полный набор на сервере.

**Alternatives considered**: nested `/clubs/{id}/persons` дублировал бы выдачу; старые
`/api/person` и `/api/persons` возвращают legacy payload и удаляются.

## Decision 2: Search and filters are database-side and cumulative

**Decision**: Trim name; пустой запрос не включается в criteria, непустой запрос длиной 3–255
проходит validation и применяется как case-insensitive partial match по `lastname OR firstname`.
`rankId`, `birthYear` и `clubId` добавляются через `AND`; club filter joins only active clubs.
General listing remains active-person-only and safely represents a missing/inactive club as null.

**Rationale**: Browser-side filtering не масштабируется и ломает pagination. Явный `LOWER(...)`
сохраняет поведение независимо от database collation; deterministic ordering предотвращает прыжки
строк между страницами.

**Alternatives considered**: full equality не соответствует partial search; загрузка всех персон в
браузер создаёт большой payload и stale data; snake_case params отвергнуты как несовместимые с
текущим V1/SPA style.

## Decision 3: Enrich each row in the existing list query

**Decision**: `ViewPersonDto` получает `clubId` вместе с `id`, names, birthday и rankId. Год
рождения выводится на фронтенде из birthday. Имя клуба
разрешается на фронте через закэшированные club options; assembler не делает дополнительных
запросов и list query не загружает лишние связи/агрегации.

**Rationale**: Shared table получает club options один раз на страницу/сессию; row-level
club/rank requests нарушили бы FR-012 и performance invariant. Rank labels and club names resolve
from the existing cached V1 options using row ids.

**Alternatives considered**: отдельный request на каждый club/rank — N+1; расчёт current rank
в assembler — повторная бизнес-логика вместо существующей projection; отдельный persons response
не нужен.

## Decision 4: All active-club options on a separate V1 endpoint

**Decision**: Добавить отдельный `GET /api/v1/clubs/all`. Он возвращает все активные `{id,name}`
в порядке `name,id` через `ClubRepository::all()` и отдельные `ListAllClubService`/
`ListAllClubAction`. Искусственного лимита нет. Обычный paginated
`GET /api/v1/clubs` с `personsCount` и audit groups сохраняется и не кэшируется SPA.
SPA кэширует только options в памяти/localStorage по тому же TTL-паттерну, что ranks/years.

**Rationale**: Фильтр должен видеть весь справочник активных клубов за один запрос и не должен
тащить поля таблицы клубов. Отдельный route/use case не смешивает полную выдачу с пагинированной
табличной выдачей; кэширование ограничено только справочником options.

**Alternatives considered**: `perPage=1000` возвращал бы пагинационные поля и смешивал два
сценария; последовательная загрузка страниц добавляет round trips; query-параметры на обычном
route усложняют его контракт и повышают риск случайного кэширования табличной выдачи.

## Decision 5: One shared Vue 3 table

**Decision**: Выделить `PersonTable.vue` из текущей таблицы `ClubDetailsPage.vue`. Страница списка
отвечает за фильтры, API и pagination; таблица отвечает за columns, null-safe rendering and links.
Club details passes its already-loaded rows and pagination to the same component.

**Rationale**: Это устраняет расхождение колонок и ссылок между списком и клубом без создания
спекулятивного универсального data-grid.

**Alternatives considered**: копирование разметки сохраняет drift; конфигурируемая таблица всех
сущностей добавляет сложность за пределами этой фичи.

## Decision 6: Request lifecycle and UX states

**Decision**: Text search uses 300 ms debounce and minimum 3 non-space characters. Filter changes
reset page to 1. A monotonically increasing request sequence (and cancellation where supported)
prevents an older response from replacing the newest state. Loading, validation, empty and retryable
API-error states are explicit.

**Rationale**: Быстрые изменения фильтров иначе дают stale rows и лишние запросы. Поведение
соответствует existing `listingModels`/SPA auth patterns.

## Decision 7: Remove the list boundary only after migrating consumers

**Decision**: Remove the `/persons` route, Vue2 list mount/template, `/api/person` and `/api/persons`
controllers/routes and list-only `LegacySearchPersonDto`, `ListLegacyPersons` and
`ListLegacyPersonsService`. Before deletion, event/cup/console consumers use a target Application
non-paginated criteria use case backed by existing `PersonRepository` and the existing legacy view
projection required by their Blade contracts. Keep detail/create/edit/payment/prompt/rank routes
and `LegacyViewPersonDto` where those flows still depend on it.

**Rationale**: Removing only HTTP routes leaves dead classes and would not satisfy the clarified
scope; removing detail/form code would break the incremental migration.

**Alternatives considered**: redirecting `/persons` contradicts the clarified 404 behavior; leaving
the legacy service would keep two list boundaries and fail the zero-reference success criterion.
