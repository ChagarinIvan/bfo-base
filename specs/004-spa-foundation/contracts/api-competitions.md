# API-контракт: соревнования V1

## GET /api/v1/competitions

Публичный список соревнований. Query: `year`, `page`, `per_page`.

Тело ответа — прямой JSON-массив `ViewCompetitionDto`, без `data`, `meta` и
`links`. Пагинация передаётся только заголовками:

```text
X-Pagination-Total
X-Pagination-Per-Page
X-Pagination-Current-Page
X-Pagination-Last-Page
```

Без валидного Bearer-токена поля `created` и `updated` отсутствуют. При валидном
токене они добавляются сериализатором групп.

## POST /api/v1/competitions

Требует `Authorization: Bearer {token}`. Тело содержит `name`, `description`,
`from`, `to`, `mass`. Успешный ответ `201` — прямой `ViewCompetitionDto` без
`data`. Ошибки возвращаются как `{ "errors": [...] }`.

Пагинация реализуется отдельным `Pagination` DTO и `Slice`; контроллер не читает
`Request` и не вычисляет срез вручную.
