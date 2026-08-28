# API Контракт: Стандартный Response Envelope (V1)

**Версия API**: v1 | **Префикс**: `/api/v1/`

Все эндпоинты `/api/v1/*` **обязаны** использовать следующие форматы ответов.

---

## Коллекция (список ресурсов)

```json
{
  "data": [
    { "...поля ресурса..." }
  ],
  "meta": {
    "pagination": {
      "total": 42,
      "per_page": 20,
      "current_page": 1,
      "last_page": 3
    }
  },
  "links": {
    "first": "https://example.com/api/v1/competitions?page=1",
    "last":  "https://example.com/api/v1/competitions?page=3",
    "prev":  null,
    "next":  "https://example.com/api/v1/competitions?page=2"
  }
}
```

HTTP статус: **200 OK**

---

## Единичный ресурс

```json
{
  "data": {
    "...поля ресурса..."
  }
}
```

HTTP статус: **200 OK** (чтение) или **201 Created** (создание)

---

## Ошибки валидации (422)

```json
{
  "errors": [
    {
      "code": "validation_error",
      "field": "name",
      "message": "The name field is required."
    },
    {
      "code": "validation_error",
      "field": "from",
      "message": "The from field must be a valid date."
    }
  ]
}
```

HTTP статус: **422 Unprocessable Entity**

---

## Ошибка аутентификации (401)

```json
{
  "errors": [
    {
      "code": "unauthenticated",
      "message": "Unauthenticated."
    }
  ]
}
```

HTTP статус: **401 Unauthorized**

---

## Ресурс не найден (404)

```json
{
  "errors": [
    {
      "code": "not_found",
      "message": "Resource not found."
    }
  ]
}
```

HTTP статус: **404 Not Found**

---

## Реализация

| Класс | Назначение |
|---|---|
| `App\Bridge\Laravel\Http\Resources\Api\V1\AbstractV1Resource` | Базовый для единичных ресурсов: оборачивает в `{ "data": {...} }` |
| `App\Bridge\Laravel\Http\Resources\Api\V1\AbstractV1Collection` | Базовый для коллекций: строит `data` + `meta.pagination` + `links` |
| `App\Bridge\Laravel\Exceptions\Handler` | Перехватывает `ValidationException` → 422 envelope; `AuthenticationException` → 401 envelope |
