# API-контракт V1: прямые DTO-ответы

Все успешные ответы `/api/v1/*` сериализуются напрямую, без поля `data`.

Коллекция:

```json
[
  { "id": "42", "name": "Чэмпіянат" }
]
```

Пагинация не входит в JSON. Она передаётся только headers:

```text
X-Pagination-Total: 42
X-Pagination-Per-Page: 20
X-Pagination-Current-Page: 1
X-Pagination-Last-Page: 3
```

Pagination links отсутствуют.

Единичный DTO также возвращается напрямую:

```json
{ "id": "42", "name": "Чэмпіянат" }
```

Ошибки используют отдельный envelope:

```json
{
  "errors": [
    { "code": "validation_error", "field": "name", "message": "The name field is required." }
  ]
}
```

V1 не использует глобальный `Handler` для преобразования ошибок.
