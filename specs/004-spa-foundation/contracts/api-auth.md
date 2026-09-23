# API Контракт: Аутентификация (V1)

**Версия API**: v1 | **Провайдер**: `ApiV1RoutesServiceProvider`

Большинство эндпоинтов аутентификации не используют web-middleware и CSRF-защиту.
Переход в Horizon — исключение: он создаёт web-сессию, но требует Bearer-токен и
разрешён только пользователю из `HORIZON_AUTHORIZED_USER_ID`.

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
  "token": "1|abc123xyz...",
  "token_type": "Bearer"
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
**Application**: `LoginAction` передаёт входные данные в Application-слой, а преобразование
`AccessToken` в `ViewTokenDto` выполняет `LoginAssembler`. Sanctum-реализация находится в
Infrastructure.

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

## POST /api/v1/auth/horizon-session

**Назначение**: создать same-origin web-сессию для перехода в Horizon после
Bearer-аутентификации SPA.

**Auth**: требуется (`Authorization: Bearer {token}`)

### Request

Тело запроса отсутствует. Токен передаётся только в заголовке Authorization.

### Response 204 No Content

Сервер устанавливает зашифрованную HttpOnly cookie web-сессии. Токен не
возвращается в теле ответа и не передаётся в URL.

### Response 401 Unauthorized

Возвращается при отсутствующем, отозванном или истёкшем Bearer-токене.

### Response 403 Forbidden

Возвращается с кодом `horizon_access_denied`, если пользователь не совпадает с
`HORIZON_AUTHORIZED_USER_ID`.

### Security

- При наличии Bearer-заголовка API проверяет именно этот токен и не подменяет
  его существующей web-сессией.
- При создании сессии сервер меняет её идентификатор.
- SPA сначала получает ответ 204, затем открывает `/horizon/` обычной
  навигацией браузера.
- `DELETE /api/v1/auth/logout` отзывает текущий Bearer-токен и завершает
  созданную для Horizon web-сессию.
- Сам `/horizon/` применяет ту же проверку пользователя, поэтому web-сессия
  другого аккаунта не даёт доступа даже при прямом переходе по URL.

**Контроллер**:
`App\Bridge\Laravel\Http\Controllers\Api\V1\Auth\StartHorizonSessionAction`

---

## GET /api/v1/auth/horizon-access

**Назначение**: получить право текущего пользователя на отображение ссылки Horizon
в SPA.
**Auth**: требуется (`Authorization: Bearer {token}`)

### Response 200 OK

```json
{ "allowed": true }
```

Значение вычисляется по `HORIZON_AUTHORIZED_USER_ID`; оно служит только для
интерфейса. Серверная проверка остаётся обязательной.

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
