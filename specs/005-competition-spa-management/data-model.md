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

### CompetitionSearchDto

Расширяет существующий список.

| Поле | Валидация | Default |
|---|---|---|
| year | Допустимый год системы | Текущий год. |
| name | Trimmed, 3–255 символов, optional | Нет фильтра. |
| date | `YYYY-MM-DD`, optional | Нет фильтра. |
| page / per_page | Стандартная pagination validation | 1 / 20. |

`ListCompetitions` command переносит DTO в существующий list use case.

### CompetitionDto и существующие commands

`CompetitionDto` содержит `name`, `description`, `from`, `to`, `mass` и одинаково используется create/PUT. Server rule: `to >= from`. `UpdateCompetitionInfo` получает id, DTO и UserId; `DisableCompetition` — id и UserId.

### SearchEventDto

`EventSearchDto` переименовывается в `SearchEventDto` во всех существующих consumers. Для V1 action поле `competitionId` обязательно и положительно; остальные прежние поля (`year`, `flagId`, `notRelatedToCup`) остаются опциональными для legacy callers.

`ListEventsAction` принимает `SearchEventDto`, создаёт существующий `ListEvents` command и вызывает `ListEventsService::executeForApi()`. Метод возвращает компактный `ViewEventListDto`; существующий `execute()` и `ViewEventDto` продолжают обслуживать Blade.

## View DTOs

### ViewCompetitionDto

Существующий object: `id`, `name`, `description`, `from`, `to`, `year`, `mass`; authenticated representation также содержит `created`, `updated`.

### ViewEventListDto

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
- **CompetitionFormState**: поля соревнования, field errors и submit state, общие для create/edit.
- **DeleteConfirmation**: выбранное соревнование, visibility и submit state; DELETE возможен только после confirm.

## Read relations

```text
Competition 1 ── * Event
Event 1 ── * Distance ── * ProtocolLine
```

`EventRepository::byCriteria()` для list path добавляет количество участников через один `withCount` для всего списка. `ListEventsService::executeForApi()` передаёт полученные Event в `EventAssembler::toViewEventListDto()`; flags и cups пока не входят в V1 DTO.
