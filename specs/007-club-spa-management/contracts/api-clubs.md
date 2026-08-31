# API-контракт: Clubs V1

**Base path**: `/api/v1`

**Соглашение**: resource — прямой JSON object, collection — прямой JSON array, пагинация —
`X-Pagination-*`, ошибки — `{ "errors": [...] }`.

## GET /clubs

Публичный постраничный список активных клубов.

| Параметр | Обязателен | Валидация | Значение |
|---|---:|---|---|
| name | нет | trim; 3–255 символов | Case-insensitive фрагмент названия. |
| page | нет | integer, min 1 | Default 1. |
| perPage | нет | integer, 1–100 | Default 20. |

Пустой trimmed `name` снимает фильтр. Непустое значение короче трёх символов возвращает 422 с
`field=name`. Порядок: `name ASC`, затем `id ASC`.

Response 200:

```json
[
  {
    "id": "42",
    "name": "КСА Арыён",
    "personsCount": 17
  }
]
```

`personsCount` считает только active persons. Валидный Bearer добавляет `created` и `updated`.
Headers:

```text
X-Pagination-Total
X-Pagination-Per-Page
X-Pagination-Current-Page
X-Pagination-Last-Page
```

## GET /clubs/{clubId}

Публичный active Club. Response 200 — прямой `ViewClubDto`; optional valid Bearer добавляет
impressions. Missing/inactive id возвращает 404 error envelope.

## POST /clubs

Требует `Authorization: Bearer {token}`.

```json
{ "name": "КСА Арыён" }
```

Response 201 — созданный `ViewClubDto` с impressions. Ошибки: 401; 422 для required/max и
normalized duplicate. Field-level ответ:

```json
{
  "errors": [
    { "code": "validation_error", "field": "name", "message": "..." }
  ]
}
```

## PUT /clubs/{clubId}

Требует Bearer и то же body/validation, что POST. Response 200 — обновлённый `ViewClubDto`.
Переименование в собственное normalized name допустимо. Duplicate другого клуба — 422 `name`;
missing/inactive — 404; без токена — 401.

## Поля ViewClubDto

| Поле | Public | Authenticated |
|---|---:|---:|
| id, name, personsCount | да | да |
| created, updated | отсутствуют | да |
| normalizeName | отсутствует | отсутствует |
