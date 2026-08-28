# API Контракт: Соревнования (V1)

**Версия API**: v1 | **Провайдер**: `ApiV1RoutesServiceProvider`

---

## GET /api/v1/competitions

**Назначение**: публичный список соревнований с фильтрацией по году
**Auth**: не требуется

### Request

```http
GET /api/v1/competitions?year=2026&page=1&per_page=20
```

**Query параметры**:

| Параметр | Тип    | Обязательный | Default        | Описание |
|----------|--------|-------------|----------------|----------|
| `year`   | string | нет         | текущий год    | Фильтр по году (напр. `"2026"`) |
| `page`   | int    | нет         | `1`            | Номер страницы |
| `per_page`| int   | нет         | `20`           | Размер страницы (макс. 100) |

### Response 200 OK

```json
{
  "data": [
    {
      "id": "42",
      "name": "Чемпионат Беларуси по спортивному ориентированию",
      "description": "Лесная трасса, спринт",
      "from": "2026-05-10",
      "to": "2026-05-11",
      "year": 2026,
      "mass": false
    },
    {
      "id": "43",
      "name": "Открытый чемпионат Минска",
      "description": "Городская трасса",
      "from": "2026-06-01",
      "to": "2026-06-01",
      "year": 2026,
      "mass": true
    }
  ],
  "meta": {
    "pagination": {
      "total": 35,
      "per_page": 20,
      "current_page": 1,
      "last_page": 2
    }
  },
  "links": {
    "first": "https://bfo.by/api/v1/competitions?page=1",
    "last":  "https://bfo.by/api/v1/competitions?page=2",
    "prev":  null,
    "next":  "https://bfo.by/api/v1/competitions?page=2"
  }
}
```

**Контроллер**: `App\Bridge\Laravel\Http\Controllers\Api\V1\Competition\ListCompetitionsAction`
**Application**: `ListCompetitionsService::execute(new ListCompetitions(CompetitionSearchDto))`
**Пагинация**: `LengthAwarePaginator` над результирующей коллекцией (см. `research.md` §4)
**Сортировка**: по `from` DESC (задаётся в `EloquentCompetitionRepository::byCriteria`)

**Поля по контексту авторизации**: endpoint остаётся публичным. Без валидного Bearer-токена
ресурс содержит только публичные поля (`id`, `name`, `description`, `from`, `to`, `year`, `mass`).
При наличии валидного Bearer-токена каждый ресурс дополнительно содержит `created` и `updated`.

---

## POST /api/v1/competitions

**Назначение**: создание нового соревнования
**Auth**: требуется (`Authorization: Bearer {token}`)

### Request

```http
POST /api/v1/competitions
Authorization: Bearer 1|abc123xyz...
Content-Type: application/json

{
  "name": "Чемпионат Беларуси",
  "description": "Лесная трасса",
  "from": "2026-09-15",
  "to": "2026-09-16",
  "mass": false
}
```

**Поля тела запроса**:

| Поле          | Тип    | Обязательный | Правила |
|---------------|--------|-------------|---------|
| `name`        | string | да          | max:255 |
| `description` | string | да          | max:255 |
| `from`        | string | да          | валидная дата (Y-m-d) |
| `to`          | string | да          | валидная дата (Y-m-d), не раньше `from` |
| `mass`        | bool   | нет         | default: false |

### Response 201 Created

```json
{
  "data": {
    "id": "99",
    "name": "Чемпионат Беларуси",
    "description": "Лесная трасса",
    "from": "2026-09-15",
    "to": "2026-09-16",
    "year": 2026,
    "mass": false
  }
}
```

### Response 401 Unauthorized (нет токена)

```json
{
  "errors": [
    { "code": "unauthenticated", "message": "Unauthenticated." }
  ]
}
```

### Response 422 Unprocessable Entity (ошибки валидации)

```json
{
  "errors": [
    { "code": "validation_error", "field": "name",        "message": "The name field is required." },
    { "code": "validation_error", "field": "description", "message": "The description field is required." }
  ]
}
```

**Контроллер**: `App\Bridge\Laravel\Http\Controllers\Api\V1\Competition\CreateCompetitionAction`
**Application**: `AddCompetitionService::execute(new AddCompetition(CompetitionDto, UserId))`
**UserId**: берётся из `$request->user()->id` (без фасадов)
**Валидация**: использует `CompetitionDto::requestValidationRules()` напрямую

---

## Регистрация маршрутов

```php
// ApiV1RoutesServiceProvider
$this->route->prefix('api/v1/competitions')->group(function (): void {
    // публичный
    $this->route->get('', ListCompetitionsAction::class);

    // приватный
    $this->route->middleware('auth:sanctum')->group(function (): void {
        $this->route->post('', CreateCompetitionAction::class);
    });
});
```
