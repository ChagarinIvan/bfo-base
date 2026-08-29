# API Контракт: Аутентификация (V1)

**Версия API**: v1 | **Провайдер**: `ApiV1RoutesServiceProvider`

Все эндпоинты аутентификации не имеют CSRF-защиты (не используют web-middleware).
Маршруты регистрируются без middleware `web`; только защищённые эндпоинты требуют
middleware `auth:sanctum`.

Sanctum personal access tokens имеют серверный срок действия `1440` минут (один
день), заданный в `config/sanctum.php`. Refresh-токены и endpoint
`/api/v1/auth/refresh` отсутствуют. После истечения токена клиент должен выполнить
login заново.

---

## POST /api/v1/auth/login

**Назначение**: вход пользователя, получение Bearer-токена
**Auth**: не требуется

### Request

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "secret"
}
```

### Response 200 OK

```json
{
  "data": {
    "token": "1|abc123xyz...",
    "token_type": "Bearer"
  }
}
```

### Response 422 Unprocessable Entity (невалидные поля)

```json
{
  "errors": [
    { "code": "validation_error", "field": "email", "message": "The email field is required." }
  ]
}
```

### Response 401 Unauthorized (неверные учётные данные)

```json
{
  "errors": [
    { "code": "invalid_credentials", "message": "The provided credentials are incorrect." }
  ]
}
```

**Контроллер**: `App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\LoginAction`
**Application**: `LoginAction` передаёт command в `LoginService`; application service зависит
только от доменного `LoginAuthenticator`, а преобразование `AccessToken` в `ViewTokenDto`
выполняет `LoginAssembler`. Sanctum-реализация находится в Infrastructure.

---

## DELETE /api/v1/auth/logout

**Назначение**: выход, отзыв текущего токена
**Auth**: требуется (`Authorization: Bearer {token}`)

### Request

```http
DELETE /api/v1/auth/logout
Authorization: Bearer 1|abc123xyz...
```

### Response 204 No Content

*(пустое тело)*

### Response 401 Unauthorized (нет/невалидный токен)

```json
{
  "errors": [
    { "code": "unauthenticated", "message": "Unauthenticated." }
  ]
}
```

**Контроллер**: `App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\LogoutAction`
**Реализация**: `$request->user()->currentAccessToken()->delete()`

---

## GET /api/v1/users

**Назначение**: полный список аутентифицированных пользователей для разрешения author ids
**Auth**: требуется (`Authorization: Bearer {token}`)

### Request

```http
GET /api/v1/users
Authorization: Bearer 1|abc123xyz...
```

### Response 200 OK

```json
{
  "data": [
    {
      "id": 1,
      "name": null,
      "email": "ivan@example.com"
    }
  ]
}
```

### Response 401 Unauthorized

```json
{
  "errors": [
    { "code": "unauthenticated", "message": "Unauthenticated." }
  ]
}
```

**Контроллер**: `App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\ListUsersAction`

Возвращается полный список пользователей без фильтров и пагинации. В DTO отсутствуют
пароли и внутренние поля.

---

## Регистрация маршрутов

```php
// ApiV1RoutesServiceProvider
$router->prefix('api/v1')->post('auth/login', LoginAction::class);
$router->prefix('api/v1')->middleware(AuthenticateApiV1::class)->group(function (): void {
    $router->delete('auth/logout', LogoutAction::class);
    $router->get('users', ListUsersAction::class);
});
```
