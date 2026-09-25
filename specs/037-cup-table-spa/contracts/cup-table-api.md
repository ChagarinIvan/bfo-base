# Контракт V1: таблица кубка

## Request

`GET /api/v1/cups/{cupId}/tables/{groupId}`

`groupId` uses the existing `CupGroup` identifier format returned by the cup view
(including legacy identifiers such as `M_0_`) and is mandatory for every table
request.

Query parameters:

- `name` — optional, empty value means no predicate, non-empty value must have at
  least 3 characters;
- `page` — optional, 1-based;
- `perPage` — optional, из стандартного набора пагинации.

Все параметры используют camelCase. Endpoint доступен через optional API auth;
authenticated-only metadata не должен появляться в публичных строках.

## Response

Ответ содержит один типизированный объект `stages` и полный массив `rows` с
полями из `data-model.md`; pagination не используется. Ошибки: 404 для
несуществующего кубка/группы и 422 для имени длиной 1–2 символа или отсутствующего
обязательного groupId.

Каждая stage cell содержит `stageId`, `distanceId` и `protocolLineId`. Для ссылки
на `/app/events/{eventId}?distanceId={distanceId}#protocol-line-{protocolLineId}`
frontend берёт `eventId` из связанного этапа.

Старые web routes `/{cup}/{group}/table`, `table-export` и связанные Blade-шаблоны
удалены; просмотр таблицы выполняется через SPA и V1 API.
