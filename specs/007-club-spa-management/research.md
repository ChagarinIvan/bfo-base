# Исследование: управление клубами в SPA

**Дата**: 2026-08-31 | **Фича**: `007-club-spa-management`

## V1 API и пагинация

**Решение**: добавить `GET /api/v1/clubs`, `GET /api/v1/clubs/{clubId}`,
`POST /api/v1/clubs`, `PUT /api/v1/clubs/{clubId}` и
`GET /api/v1/persons?clubId={clubId}`. Collections остаются прямыми массивами, metadata — в
`X-Pagination-*`; `Pagination` задаёт default 20 и диапазон 1–100.

**Обоснование**: это фактический контракт V1 соревнований и событий, уже поддержанный `ApiAction`,
`Slice`, Axios и frontend helper.

**Альтернативы**: nested `/clubs/{id}/persons` лучше выражает ресурс, но расходится с явным
`clubId` contract спеки и существующим event-list pattern. Envelope `{data, meta}` отвергнут как
несовместимый с текущим V1.

## Разделение target и legacy list paths

**Решение**: канонические `SearchClubDto`/`ListClubs`/`ListClubsService` и
`SearchPersonDto`/`ListPersons`/`ListPersonsService` обслуживают только paginated V1. Существующие
непагинированные пути становятся `LegacySearchClubDto`/`ListLegacyClubs`/
`ListLegacyClubsService` и `LegacySearchPersonDto`/`ListLegacyPersons`/
`ListLegacyPersonsService`; они продолжают обслуживать Blade, console и `/api/persons`. Repository
получает отдельный `paginate(Criteria)` рядом с сохраняемым `byCriteria()`.

**Обоснование**: текущие club/person list services возвращают богатые массивы и используются
формами персонов, событиями, кубками, console-командами и legacy API. Изменение их return type
сломает unrelated paths, а использование их в V1 оставит неограниченные выборки.

**Альтернативы**: параметр `paginated` в одном service создаёт два return type; полное удаление
старого пути невозможно из-за найденных consumers.

## Target и legacy view DTO

**Решение**: существующий `ViewClubDto` очищается от `normalizeName` и продолжает содержать `id`,
`name`, `personsCount`, authenticated `created`/`updated`; отдельный `LegacyViewClubDto` не
создаётся. Единственный найденный потребитель `normalizeName`, `RendersEventDistance`, строит
нормализованный индекс клубов из `ViewClubDto::name` через существующий
`NormalizedNameClubFinder::normalizeName()`. Новый `ViewPersonDto` содержит `id`, `lastname`,
`firstname`, nullable integer `birthYear` и authenticated impressions; прежний rich DTO становится
`LegacyViewPersonDto`. Только `PersonAssembler` получает отдельные
`toViewPersonDto`/`toLegacyViewPersonDto` mapping methods. Существующие add/view/update person
services продолжают возвращать legacy projection.

**Обоснование**: `normalizeName` — производное значение, а не самостоятельные данные клуба;
дублировать из-за него весь club DTO не требуется. Citizenship, payments, protocol lines и другие
legacy person fields не нужны новым экранам и не должны становиться случайным публичным контрактом.
Компактная person projection упрощает query и предотвращает N+1.

**Альтернативы**: отдельный `LegacyViewClubDto` ради одного производного поля создаёт ненужный DTO
и второй mapping path. Один person DTO с nullable-полями или множеством serialization groups
скрывает разные модели данных и усложняет типы SPA; новый `ClubPersonDto` противоречит принятому
каноническому `ViewPersonDto`.

## Active-only счётчик и запрос персонов

**Решение**: club list/detail считает только `person.active=true`. Person pagination всегда
ограничивается `person.active=true`; опциональный `clubId` дополнительно ограничивает выдачу и
требует active parent club. Без `clubId` endpoint возвращает общий active-person list, пригодный
для будущей SPA-страницы персонов. Сортировка `lastname ASC, firstname ASC, id ASC`. Clubs
сортируются `name ASC, id ASC`.

**Обоснование**: счётчик всегда совпадает с доступным списком, отключённые записи не раскрываются,
а tie-breaker исключает повторы и пропуски между страницами.

**Альтернативы**: текущий unconstrained `withCount('persons')` может считать отключённых;
сортировка без id нестабильна при одинаковых именах.

## Поиск клуба по названию

**Решение**: отдельный `SearchClubDto` trim-ит `name`, превращает пустую строку в отсутствие
фильтра и валидирует 3–255 символов. Infrastructure применяет case-insensitive substring matching.
SPA переиспользует minimum 3, debounce 300 ms, page reset, field-error mapping и latest-request
guard; короткое значение не отправляется.

**Обоснование**: полностью повторяет принятый UX соревнований и закрывает race устаревших ответов.

**Альтернативы**: client-only filtering требует загрузить все клубы; exact match не соответствует
поисковому сценарию; копирование helpers создаёт расхождение двух экранов.

## Создание, редактирование и уникальность имени

**Решение**: одна `ClubDto` и одна SPA-форма используются для POST/PUT.
`PreventDuplicateClubFactory` проверяет create по normalized name. Application use case под
транзакцией блокирует клуб, готовит нормализованный `ClubInput` и передаёт его в доменный
`ClubUpdater`. `PreventDuplicateClubUpdater` проверяет конфликт по имени и нормализованному имени,
а `StandardClubUpdater` собирает `Impression` и вызывает `Club::updateInfo(ClubInfo, Impression)`.
При полном совпадении текущих значений сервис идемпотентно возвращает DTO без updater и сохранения.
Aggregate обновляет имя и `updated` impression, затем записывает domain event. `ClubNotFound` и name
conflict переводятся в Application errors. Бизнес-конфликт возвращается как 409 с уникальным кодом
и сообщением без `field`; `field` остаётся только у validation errors.

**Обоснование**: duplicate — доменное правило, одинаковое для create/update; action остаётся
тонким. Код бизнес-ошибки позволяет SPA перевести конфликт и показать его на уровне формы.

**Альтернативы**: проверка в Vue обходится прямым API; проверка в controller нарушает слои;
DB unique migration не выбрана, потому что схема данных явно вне scope и требует отдельного аудита
исторических normalized duplicates.

## Authenticated impressions

**Решение**: public V1 routes используют `OptionalAuthenticateApiV1`; `created` и `updated`
помечаются `Groups(['authenticated'])`. SPA загружает users только при действующем токене и
переиспользует `ImpressionDetails` с цифровой датой `YYYY-MM-DD` и временем.

**Обоснование**: serializer действительно исключает свойства из public JSON, а не маскирует их;
поведение идентично соревнованиям.

**Альтернативы**: скрыть колонки только во Vue небезопасно; отдельные public/private endpoints
дублируют контракт.

## SPA pages и повторное использование

**Решение**: создать `ClubsPage`, `ClubDetailsPage`, `CreateClubPage`, `EditClubPage` и общую
`ClubForm`. Reusable name-search, debounce, pagination, 422 mapping и not-found helpers извлекаются
из competition-only model в общий SPA module и продолжают использоваться соревнованиями.

**Обоснование**: клубный filter обязан повторять поведение соревнований, а форма create/edit имеет
одно поле и один validation contract.

**Альтернативы**: копии helpers и двух форм быстрее только в первой итерации, но гарантированно
расходятся при следующем исправлении.

## Миграция URL и очистка legacy

**Решение**: `/clubs`, `/clubs/create`, `/clubs/{clubId}/show` отвечают 301 на соответствующие
`/app/clubs*`; redirect routes не выполняют auth/business logic. `/clubs/store` удаляется. Blade
navbar и все найденные club links переводятся прямо на SPA. Club Blade actions/views/tests и
неиспользуемый `DisableClub*` удаляются. `components/club-link.blade.php` сохраняется для найденного
`@include`, но ведёт в SPA; PHP `ClubLink` component и его registration удаляются после исчезновения
последнего `<x-club-link>`.

**Обоснование**: старые закладки продолжают работать, но второго UI и старой мутации нет. Аудит
usages предотвращает удаление общих club/person services и legacy person APIs.

**Альтернативы**: 404 ломает сохранённые GET-ссылки; сохранение Blade параллельно противоречит
завершению миграции; blanket deletion ломает события, кубки, person forms и console commands.

## N+1 и проверка регрессий

**Решение**: club repository использует constrained `withCount`, person projection не загружает
payments/protocol lines/club relation, обе выдачи строятся `EloquentQueryAdapter` + `Slice`.
Request tests сравнивают query count для одной и нескольких строк; полный suite проверяет legacy
consumers после rename.

**Обоснование**: число SQL-запросов не зависит от размера страницы, а новые endpoints не используют
старый rich assembler, который загружает payments и может провоцировать лишние связи.

**Альтернативы**: ручная пагинация в action дублирует инфраструктуру; mapping rich DTO затем
удаление полей сохраняет дорогие запросы.
