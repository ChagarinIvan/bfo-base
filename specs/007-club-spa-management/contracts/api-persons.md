# API-контракт: Persons V1 для страницы клуба

**Endpoint**: `GET /api/v1/persons?clubId={clubId}`

**Access**: public с optional Bearer

**Соглашение**: direct JSON array + `X-Pagination-*`; errors — `{ "errors": [...] }`.

## Query

| Параметр | Обязателен | Валидация | Значение |
|---|-----------:|---|---|
| clubId |        нет | integer, min 1 | Active Club, чьи active persons запрошены. |
| page |        нет | integer, min 1 | Default 1. |
| perPage |        нет | integer, 1–100 | Default 20. |

`club_id` не является alias и не принимается. Missing/invalid `clubId` даёт 422. Для missing или
inactive Club endpoint возвращает пустой массив; detail page получает 404 через отдельный
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
`ViewPersonDto`; старый rich DTO называется `LegacyViewPersonDto`. Если эти консьюмеры есть в этом репозитории
