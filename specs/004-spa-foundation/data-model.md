# Data Model: Фронтенд-фундамент — постепенный переход на SPA

**Phase 1 output** | **Дата**: 2026-08-29

---

## Существующие доменные сущности (не модифицируются)

### Competition (Domain)

```
App\Domain\Competition\Competition
  id            int         PK
  name          string      max:255
  description   string|null max:255
  from          Carbon      дата начала (Y-m-d)
  to            Carbon      дата окончания (Y-m-d)
  active        bool        false = удалено (soft delete)
  mass          bool        флаг массового старта
  created       Impression  { at: datetime, by: int (user_id) }
  updated       Impression  { at: datetime, by: int (user_id) }
```

### ViewCompetitionDto (Application read-model)

```
App\Application\Dto\Competition\ViewCompetitionDto
  id            string      (из int)
  name          string
  description   string
  from          string      "Y-m-d"
  to            string      "Y-m-d"
  year          int
  mass          bool
  created       ImpressionDto  { at: string, by: string }
  updated       ImpressionDto  { at: string, by: string }
```

### CompetitionDto (Application input)

```
App\Application\Dto\Competition\CompetitionDto
  name          string      required, max:255
  description   string      required, max:255
  from          string      required, валидная дата
  to            string      required, валидная дата
  mass          bool        optional, default: false
```

Правила валидации уже определены в `CompetitionDto::requestValidationRules()` —
V1-контроллер использует их напрямую.

### User (Domain)

```
App\Infrastructure\Sanctum\SanctumUser                   (существующая доменная модель; без Sanctum-трейта)
  id            int         PK
  name          string
  email         string      уникальный
  password      string      bcrypt hash
```

---

## Новые инфраструктурные сущности

### SanctumUser (Infrastructure adapter)

```
App\Infrastructure\Sanctum\SanctumUser  (extends Domain User + HasApiTokens)
  auth provider model для Bearer API
  создаёт и отзывает PersonalAccessToken
```

Domain User не импортирует Sanctum и не зависит от Infrastructure.

### PersonalAccessToken (Sanctum)

```
personal_access_tokens                  (таблица Sanctum, публикуется через artisan)
  id                bigint    PK
  tokenable_type    string    "App\Infrastructure\Sanctum\SanctumUser"
  tokenable_id      bigint    user.id
  name              string    название токена (напр. "spa-token")
  token             string    SHA-256 хеш токена
  abilities         text|null JSON массив abilities (null = все)
  last_used_at      timestamp
  expires_at        timestamp      (TTL 1440 минут; задаётся config/sanctum.php)
  created_at        timestamp
  updated_at        timestamp
```

---

Refresh token как отдельная сущность отсутствует. После истечения `expires_at`
Sanctum отклоняет Bearer-токен, клиент получает `401` и выполняет login заново.

## API Response Types (контракты сериализации)

### ViewCompetitionDto (V1)

Сериализует `ViewCompetitionDto` в API-ответ.

```
{
  "id": "42",
  "name": "Чемпионат Беларуси",
  "description": "Лесной спринт",
  "from": "2026-05-10",
  "to": "2026-05-11",
  "year": 2026,
  "mass": false
}
```

Поля `created` и `updated` (Impression) добавляются только если текущий HTTP-запрос
содержит валидный Bearer-токен. Для анонимного запроса ресурс содержит только
публичные поля.

### ViewTokenDto (V1)

```
{
  "token": "1|abc123...",
  "token_type": "Bearer"
}
```

### AuthUserDto (V1)

```
{
  "id": 1,
  "name": "Ivan Ivanov",
  "email": "ivan@example.com"
}
```

---

## Валидация на границе API

Все V1-эндпоинты получают входные DTO через общий API action adapter.
Ошибки валидации (422) оборачиваются в error envelope:

```json
{
  "errors": [
    { "code": "validation_error", "field": "name",  "message": "The name field is required." },
    { "code": "validation_error", "field": "from",  "message": "The from field must be a valid date." }
  ]
}
```

Трансформация `ValidationException` → error envelope выполняется явно на API boundary
в `ApiAction`; глобальный exception handler для этого не используется.

---

## Frontend State Model (TypeScript)

### AuthUser

```typescript
interface AuthUser {
  id: number
  name: string
  email: string
}
```

### Competition

```typescript
interface Competition {
  id: string
  name: string
  description: string
  from: string        // "YYYY-MM-DD"
  to: string          // "YYYY-MM-DD"
  year: number
  mass: boolean
  created?: string       // доступно только при валидной авторизации
  updated?: string       // доступно только при валидной авторизации
}
```

`to` не может быть раньше `from`; равные даты разрешены.

### CreateCompetitionForm

```typescript
interface CreateCompetitionForm {
  name: string
  description: string
  from: string
  to: string
  mass: boolean
}
```

### ApiPagination

```typescript
interface ApiPagination {
  total: number
  per_page: number
  current_page: number
  last_page: number
}

interface ApiCollectionResponse<T> {
  data: T[]
  meta: { pagination: ApiPagination }
  links: { first: string; last: string; prev: string | null; next: string | null }
}

interface ApiResourceResponse<T> {
  data: T
}

interface ApiError {
  code: string
  message: string
  field?: string
}

interface ApiErrorResponse {
  errors: ApiError[]
}
```

---

## Состояния Vue Router (защита маршрутов)

```
/app/login             → публичный (redirect если уже auth)
/app/competitions      → публичный (анонимный доступ)
/app/competitions/create → защищённый (redirect → /app/login если !isAuthenticated)
```

Navigation guard в `router/index.ts` проверяет `useAuthStore().isAuthenticated`
перед каждым переходом на маршруты с `meta.requiresAuth: true`.
