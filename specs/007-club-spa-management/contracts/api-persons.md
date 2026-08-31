# API-контракт: Persons V1 для страницы клуба

**Endpoint**: `GET /api/v1/persons?clubId={clubId}`

**Access**: public с optional Bearer

**Соглашение**: direct JSON array + `X-Pagination-*`; errors — `{ "errors": [...] }`.

## Query

| Параметр | Обязателен | Валидация | Значение |
|---|-----------:|---|---|
| clubId |        нет | integer, min 1 | Опционально ограничивает выдачу active persons указанным active Club. |
| page |        нет | integer, min 1 | Default 1. |
| perPage |        нет | integer, 1–100 | Default 20. |

Без `clubId` endpoint возвращает общий постраничный список active persons — это задел для будущей
SPA-страницы персонов и следующих фильтров. `club_id` не является alias и не принимается;
некорректно переданный `clubId` или `club_id` даёт 422. Для missing или inactive Club при переданном
`clubId` endpoint возвращает пустой массив; detail page получает 404 через отдельный
`GET /clubs/{clubId}`.

## Response 200

```json
[
  {
    "id": "56",
    "lastname": "Іваноў",
    "firstname": "Ян",
    "birthYear": 2001
  },
  {
    "id": "57",
    "lastname": "Пятрова",
    "firstname": "Ганна",
    "birthYear": null
  }
]
```

Порядок всегда `lastname ASC`, `firstname ASC`, `id ASC`. Inactive persons отсутствуют. Полная
дата рождения и остальные rich person fields отсутствуют. Валидный Bearer добавляет к каждой
строке `created` и `updated`.

Pagination headers идентичны Clubs V1. Данные для строки не требуют отдельного запроса на person.

## Совместимость

Legacy `/api/person` и `/api/persons` не изменяются и продолжают возвращать прежние форматы своим
consumers. Новый endpoint существует только под `/api/v1/persons` и использует компактный
`ViewPersonDto`; старый rich DTO называется `LegacyViewPersonDto`. Существующие consumers rich DTO
обновляются при rename только в imports/types и не переводятся на V1 в рамках этой фичи.

`GET /api/v1/persons` — самостоятельный расширяемый контракт: отсутствие `clubId` означает общий
active-person list, а будущие V1 filters не изменяют семантику legacy endpoints. Новые SPA-экраны
используют только V1; legacy consumers продолжают использовать `/api/person` и `/api/persons`.
