# Quickstart: Валидация реализации

**Phase 1 output** | **Дата**: 2026-08-29

Этот документ описывает как проверить, что реализация работает корректно,
без дублирования деталей из `data-model.md` и `contracts/`.

---

## Предварительные условия

1. Проект запускается локально (`php artisan serve` на порту 8000)
2. Запущены миграции: `php artisan migrate` (включая Sanctum `personal_access_tokens`)
3. В БД есть тестовый пользователь (admin) и соревнования за текущий год
4. Фронтенд собран или дев-сервер запущен: `npm run dev:spa`

---

## Сценарий 1: Публичный список соревнований (без токена)

**Проверяет**: FR-012, FR-013, FR-014, FR-020, SC-003

```bash
curl -s http://localhost:8000/api/v1/competitions | jq .
```

**Ожидаемый результат**:
- HTTP 200
- Тело: прямой JSON-массив DTO без `data`, `meta` и `links`
- Поля каждого соревнования: `id`, `name`, `description`, `from`, `to`, `year`, `mass`
- Headers содержат `X-Pagination-Total`, `X-Pagination-Per-Page`, `X-Pagination-Current-Page`, `X-Pagination-Last-Page`

```bash
# Фильтр по году
curl -s "http://localhost:8000/api/v1/competitions?year=2025" | jq 'length'
```

**Ожидается**: число > 0 (если есть данные за 2025) или пустой массив

---

## Сценарий 2: Аутентификация (login → users → logout)

**Проверяет**: FR-008, FR-009, FR-010, FR-011, SC-006

```bash
# Шаг 1: login
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"secret"}' \
  | jq -r '.data.token')

echo "Token: $TOKEN"

# Публичный endpoint с валидным токеном возвращает дополнительные Impression-поля
curl -s http://localhost:8000/api/v1/competitions \
  -H "Authorization: Bearer $TOKEN" | jq '.data[0]'
# Ожидается: в элементе есть created и updated; без токена эти поля отсутствуют.

# Шаг 2: список пользователей (с токеном)
curl -s http://localhost:8000/api/v1/users \
  -H "Authorization: Bearer $TOKEN" | jq .

# Шаг 3: logout
curl -s -X DELETE http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer $TOKEN" -w "\nHTTP: %{http_code}\n"

# Шаг 4: список пользователей после logout (должно быть 401)
curl -s http://localhost:8000/api/v1/users \
  -H "Authorization: Bearer $TOKEN" -w "\nHTTP: %{http_code}\n" | jq .
```

**Ожидаемые результаты**:
- Шаг 1: `{ "data": { "token": "...", "token_type": "Bearer" } }`
- Шаг 2: `{ "data": [{ "id": N, "name": null, "email": "..." }] }`
- Шаг 3: HTTP 204, пустое тело
- Шаг 4: HTTP 401, `{ "errors": [{ "code": "unauthenticated", ... }] }`

После истечения 1440 минут Sanctum-токен должен давать HTTP 401. Refresh endpoint
не предусмотрен: пользователь проходит login заново, после чего SPA сохраняет новый
токен в `localStorage`.

---

## Сценарий 3: Создание соревнования (с токеном)

**Проверяет**: FR-015, FR-016, FR-017

```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"secret"}' \
  | jq -r '.data.token')

# Успешное создание
curl -s -X POST http://localhost:8000/api/v1/competitions \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Тестовое соревнование",
    "description": "Quickstart test",
    "from": "2026-12-01",
    "to": "2026-12-01",
    "mass": false
  }' | jq .
```

**Ожидается**: HTTP 201, `{ "data": { "id": "...", "name": "Тестовое соревнование", ... } }`

```bash
# Попытка без токена (должно быть 401)
curl -s -X POST http://localhost:8000/api/v1/competitions \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","description":"Test","from":"2026-01-01","to":"2026-01-01"}' \
  -w "\nHTTP: %{http_code}\n" | jq .
```

**Ожидается**: HTTP 401, error envelope

```bash
# Валидационная ошибка (пустое name)
curl -s -X POST http://localhost:8000/api/v1/competitions \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"","description":"Test","from":"2026-01-01","to":"2026-01-01"}' \
  -w "\nHTTP: %{http_code}\n" | jq .
```

**Ожидается**: HTTP 422, `{ "errors": [{ "code": "validation_error", "field": "name", ... }] }`

---

## Сценарий 4: SPA — публичная страница

**Проверяет**: FR-005, FR-006, FR-021, SC-003

1. Открыть `http://localhost:8000/app/competitions` в браузере
2. Убедиться, что страница загружается (не 404)
3. В Network-панели: запрос к `/api/v1/competitions` вернул 200
4. На странице отображается PrimeVue DataTable с соревнованиями
5. Изменить год в селекторе → список обновляется (новый запрос к API)
6. Обновить страницу (F5) → страница снова загружается корректно (не 404)

---

## Сценарий 5: SPA — приватная страница (редирект без токена)

**Проверяет**: FR-022 (navigation guard)

1. Открыть `http://localhost:8000/app/competitions/create` в режиме инкогнито
2. Убедиться, что происходит автоматический редирект на `/app/login`
3. Форма создания НЕ отображается

---

## Сценарий 6: SPA — полный цикл создания (авторизованный)

**Проверяет**: FR-021, FR-022, FR-023, SC-006

1. Открыть `http://localhost:8000/app/login`
2. Ввести учётные данные администратора → нажать "Войти"
3. Произошёл редирект на список соревнований
4. Нажать "Создать соревнование"
5. Заполнить форму → отправить
6. Успешное уведомление + редирект на список
7. Новое соревнование видно в списке
8. Нажать "Выйти" → редирект на `/app/login`
9. Попытаться открыть `/app/competitions/create` → редирект на `/app/login`

---

## Сценарий 7: PHPUnit API тесты

**Проверяет**: FR-024, SC-004, SC-005

```bash
# Только новые V1 тесты
php artisan test tests/Feature/Api/V1/ --verbose

# Весь набор (нулевые регрессии)
composer test
```

**Ожидается**: все тесты зелёные; в V1-тестах покрыты:
- `LoginActionTest`, `LogoutActionTest`, `ListUsersActionTest`: login (200), login с неверными данными (401), users (200), users без токена (401), logout (204), повторный запрос после logout (401)
- `ListCompetitionsTest`: список (200 + прямой массив DTO), фильтр по году, пагинация в headers
- `CreateCompetitionTest`: создание (201 + прямой DTO), без токена (401), валидационные ошибки (422)

---

## Сценарий 8: Frontend качество-гейт

**Проверяет**: FR-003, FR-025, SC-002

```bash
# Должен завершиться с кодом 0
npm run ci

# Можно проверить отдельно:
npm run lint        # ESLint + Prettier check
npm run typecheck   # tsc --noEmit
```

**Ожидается**: код завершения 0; время выполнения < 60 секунд

---

## Сценарий 9: SPA refresh на любом маршруте

**Проверяет**: FR-006 (Nginx конфиг)

```bash
# Прямой запрос к SPA-маршруту (симулирует обновление браузера)
curl -s -o /dev/null -w "%{http_code}" http://localhost:80/app/competitions
# Ожидается: 200 (не 404)

curl -s -o /dev/null -w "%{http_code}" http://localhost:80/app/competitions/create
# Ожидается: 200
```

---

## Что не проверяет этот документ

- Детали реализации контроллеров и ресурсов — в `tasks.md`
- Полные тест-кейсы PHPUnit — в `tasks.md`
- Конфигурацию Vite и npm-скрипты — в `FRONTEND.md`
- Инструкции по деплою на production — в `DEPLOYMENT.md`
