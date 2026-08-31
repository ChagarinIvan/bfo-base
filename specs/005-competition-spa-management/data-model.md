# Data Model: Управление соревнованиями в SPA

**Дата**: 2026-08-30 | **Фича**: `005-competition-spa-management`

## Существующие сущности

### Competition

| Поле | Тип | Правило |
|---|---|---|
| id | integer | Идентификатор соревнования. |
| name | string | Обязательное, максимум 255 символов. |
| description | string | Обязательное, максимум 255 символов. |
| from / to | date | `to` не раньше `from`; год определяется по `from`. |
| mass | boolean | Признак массового старта. |
| active | boolean | `false` означает мягко удалённое соревнование. |
| created / updated | Impression | Время и пользователь изменения. |

**Переход**: `active=true` → подтверждённое delete → `active=false`. Восстановление и физическое удаление вне фичи; inactive не возвращается list/read операциями.

### Event

| Поле/связь | Назначение |
|---|---|
| competition_id | Принадлежность одному соревнованию. |
| name, description, date | Данные таблицы этапов. |
| active | Неактивный этап не показывается. |
| protocolLines | Источник агрегированного количества участников. |

## Input DTOs и commands

### SearchCompetitionDto

Расширяет существующий список.

| Поле | Валидация | Default |
|---|---|---|
| year | Допустимый год системы | Текущий год. |
| name | Trimmed, 3–255 символов, optional | Нет фильтра. |
| date | `YYYY-MM-DD`, optional | Нет фильтра. |
| page / perPage | `page >= 1`; competition list uses shared limit, event list uses `perPage <= 20` | 1 / 20. |

`ListCompetitions` command переносит DTO в существующий list use case.

### CompetitionDto и существующие commands

`CompetitionDto` содержит `name`, `description`, `from`, `to`, `mass` и одинаково используется create/PUT. Server rule: `to >= from`. `UpdateCompetitionInfo` получает id, DTO и UserId; `DisableCompetition` — id и UserId.

### SearchEventDto

`EventSearchDto` переименовывается в `SearchEventDto` во всех существующих consumers. V1 action принимает обязательный положительный `competitionId`; остальные прежние поля (`year`, `flagId`, `notRelatedToCup`) остаются опциональными для legacy callers. Отсутствующий или мягко удалённый родитель даёт пустой список, как соревнование без активных этапов; выборка также требует `competition.active=true`.

`ListEventsAction` принимает `SearchEventDto` и event-specific `EventPagination` с максимальным
`perPage=20`, создаёт существующий `ListEvents` command и вызывает новый `ListEventsService::execute()`.
Метод возвращает `Slice<ViewEventDto>` с компактным V1 DTO. `ListLegacyEventsService::execute()` и
`LegacyViewEventDto` продолжают обслуживать Blade.

## View DTOs

### ViewCompetitionDto

Существующий object: `id`, `name`, `description`, `from`, `to`, `year`, `mass`; authenticated representation также содержит `created`, `updated`.

### ViewEventDto

```text
id: string
competitionId: string
name: string
description: string
date: YYYY-MM-DD
participantsCount: integer
created / updated: Impression (authenticated only)
```

Assembler создаёт DTO из заранее загруженного Event. В API DTO нет Eloquent/Domain объектов.

### Impression

```text
at: ISO-8601 datetime
by: string user id
```

## SPA state

- **CompetitionFilters**: year, pending name, applied name, date, pagination; pending name не уходит до трёх символов и debounce.
- **DateFilter**: общий wrapper над PrimeVue DatePicker для фильтров и create/edit-форм с
  отображением и placeholder `YYYY-MM-DD`; наружу эмитит пустое или полностью выбранное значение
  в виде строки, чтобы частичный ввод не отправлялся в API. Все даты SPA в state, API и UI используют
  только формат `YYYY-MM-DD` (`Y-m-d`), без локализованных названий месяцев.
- **CompetitionFormState**: поля соревнования, field errors и submit state, общие для create/edit.
- **DeleteConfirmation**: выбранное соревнование, visibility и submit state; DELETE возможен только после confirm.

## Read relations

```text
Competition 1 ── * Event
Event 1 ── * Distance ── * ProtocolLine
```

`EventRepository::paginate()` для V1 list path ограничивает события активным parent competition и
добавляет количество участников через один `withCount` для страницы. `ListEventsService::execute()`
передаёт полученные Event в `EventAssembler::toViewEventDto()`; flags и cups пока не входят в V1 DTO.
`byCriteria()` остаётся путём legacy service.
