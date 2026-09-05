# API-контракты: персональные промпты

Все пути находятся под `/api/v1` и используют существующий JSON serializer и pagination headers.

## List person prompts

`GET /person-prompts?personId={int}&page={int}&perPage={int}`

- Auth: required; `401` для неаутентифицированного запроса. Неактивная персона даёт пустой filtered result.
- `200`: paginated array of `{ id, personId, prompt, metaphone }`; audit fields доступны в
  authenticated projection.
- Order: `id DESC`; загружается только текущая страница.
- Invalid `personId`, `page` or `perPage`: `422` field errors.

## Create person prompt

`POST /persons/{personId}/prompts` with `{ "prompt": "..." }`

- Auth: required; `401` unauthenticated, `404` unknown person, `422` validation error.
- `201` or existing project mutation success status: created prompt projection.

## View person prompt

`GET /person-prompts/{promptId}`

- Auth: required; `401` для неаутентифицированного запроса, `200` prompt projection, `404` missing prompt.

## Update person prompt

`PUT /person-prompts/{promptId}` with `{ "prompt": "..." }`

- Auth: required; `401` unauthenticated, `404` missing prompt, `422` field validation errors.
- Success returns updated prompt projection using existing mutation response conventions.

## Delete person prompt

`DELETE /person-prompts/{promptId}`

- Auth: required; `401` unauthenticated, `404` missing prompt.
- Success sets `active=false`, publishes `PersonPromptDisabled` and returns the existing project delete
  success status with no false-success payload.

Legacy web prompt routes are not API aliases and are removed; no redirect or compatibility stub is
specified.
