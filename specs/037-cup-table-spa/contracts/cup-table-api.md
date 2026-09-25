# Контракт V1: таблица кубка

## Request

`GET /api/v1/cups/{cupId}/tables/{groupId}`

`groupId` uses the existing `CupGroup` identifier format returned by the cup view
(including legacy identifiers such as `M_0_`) and is mandatory for every table
request.

Query parameters:

- `name` — optional, empty value means no predicate, non-empty value must have at
  least 3 characters;
- `page` and `perPage` — optional slice pagination; response headers use the
  standard `X-Pagination-*` contract.

Все параметры используют camelCase. Endpoint доступен через optional API auth;
authenticated-only metadata не должен появляться в публичных строках.

## Response

Ответ — массив строк текущего среза, каждая строка содержит поля из
`CupTableRow`. Объект таблицы и метаданные этапов не передаются: SPA уже загружает
кубок отдельно и получает этапы из списка этапов. Фильтр `name` и пагинация
применяются в Application после получения рассчитанной таблицы из доменного
builder/cache. Ошибки: 404 для
несуществующего кубка, 400 если группа не поддерживается типом кубка и 422 для
невалидного формата `groupId` или имени длиной 1–2 символа.

Каждая stage cell содержит `stageId`, `distanceId` и `protocolLineId`. Для ссылки
на `/app/events/{eventId}?distanceId={distanceId}#protocol-line-{protocolLineId}`
frontend берёт `eventId` из связанного этапа.

Старые web routes `/{cup}/{group}/table`, `table-export` и связанные Blade-шаблоны
удалены; просмотр таблицы выполняется через SPA и V1 API.
