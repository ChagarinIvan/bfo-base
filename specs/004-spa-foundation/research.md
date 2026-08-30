# Research: Фронтенд-фундамент — постепенный переход на SPA

**Phase 0 output** | **Дата**: 2026-08-29 | **Фича**: `004-spa-foundation`

---

## 1. Sanctum API-токены в Infrastructure/Sanctum

**Решение**: Не добавлять `HasApiTokens` в `App\Domain\User\User`. Создать
`App\Infrastructure\Sanctum\SanctumUser`, наследующий существующую Eloquent-модель
пользователя и подключающий `HasApiTokens`; направить auth provider Laravel на этот
Infrastructure-адаптер. Настроить `sanctum.expiration` на `1440` минут (один день).

**Обоснование**: Sanctum хранит персональные токены в таблице `personal_access_tokens`
и связывает их с authenticatable-моделью через полиморфный трейт. Infrastructure-адаптер
сохраняет Sanctum-зависимость за пределами Domain; Application получает только `UserId`.

**Альтернативы**:
- Добавить `HasApiTokens` в Domain User — отклонено из-за прямого нарушения конституционной
  границы Domain
- Использовать Cookie SPA-mode Sanctum — исключён: требует CSRF-токен, не расширяем
  на мобильный клиент

**Файлы затронуты**: `app/Infrastructure/Sanctum/SanctumUser.php`, auth provider/config,
`database/migrations/XXXX_personal_access_tokens.php` (публикация из Sanctum),
`config/sanctum.php` (серверный TTL токена).

Refresh-токены и endpoint `/api/v1/auth/refresh` не добавляются. После истечения
Sanctum-токена API отвечает `401`, SPA очищает локальное состояние и предлагает
обычный login заново. Просроченные записи удаляются штатной командой
Sanctum `sanctum:prune-expired`.

---

## 2. Vite + laravel-mix сосуществование

**Решение**: Vite собирает в `public/spa/`, laravel-mix — в `public/js/` и `public/css/`.
Манифесты не конфликтуют: `public/spa/.vite/manifest.json` vs `public/mix-manifest.json`.

**Конфиг Vite**:
```ts
// vite.config.ts
{
  root: 'resources/spa',
  base: '/spa/',
  build: {
    outDir: '../../public/spa',
    emptyOutDir: true,
    rollupOptions: { input: 'resources/spa/index.html' }
  }
}
```

**Обоснование**: `base: '/spa/'` означает, что все ассеты грузятся как `/spa/assets/…` —
не пересекается с `/js/` и `/css/` laravel-mix. `emptyOutDir: true` — чисто пересобирает
только `public/spa/`, не трогая остальное.

**Альтернативы**:
- Inertia.js — слишком инвазивен, требует переписывать все контроллеры
- Полный переход на Vite сейчас — сломает Blade-фронт, вне scope

---

## 3. Nginx SPA-роутинг (`/app/*`)

**Решение**: Добавить location-блок **до** существующего `location /` в конфиге Nginx.

```nginx
# SPA routing — перед location /
location ~ ^/app(/.*)?$ {
    try_files /spa/index.html =404;
}
```

**Обоснование**: `try_files /spa/index.html =404` отдаёт `public/spa/index.html`
для любого `/app/*` URL (в т.ч. при обновлении страницы). Nginx нормализует путь
относительно `root /var/www/public`. Существующий `location /` для Blade + PHP-FPM
не затрагивается.

**Важно**: Порядок location-блоков критичен — `/app` regex должен быть до `location /`.

---

## 4. Пагинация на уровне query adapter

**Решение**: Репозиторий предоставляет `paginate(Criteria): Slice<Competition>`.
`Slice` оборачивает Pagerfanta, а инфраструктурный `EloquentQueryAdapter` переводит
offset/limit в SQL query builder. Application service маппит элементы Slice в DTO
через assembler; контроллер только задаёт `page` и `per_page` через отдельный DTO.

Для заголовков пагинации выполняется `COUNT`, а данные читаются отдельным запросом
с `LIMIT/OFFSET`. Полный `get()` по результатам фильтра и ручной `array_slice()`
запрещены для API-списков.

---

## 5. Envelope-стандарт: DTO serializer

**Решение**: DTO-ответы сериализуются общим Bridge-сериализатором с группами доступа.

`created` и `updated` помечаются группой `authenticated`; остальные поля входят в публичную группу.
`ApiDtoSerializer` выбирает группу по наличию валидного Bearer-токена и удаляет поля,
которые не входят в выбранную группу. DTO не зависят от Request или Laravel Resources.

**Error envelope** формируется явно на API boundary: Application service преобразует ожидаемые
доменные ошибки в Application-ошибки, а V1 action возвращает `JsonResponse` с нужным статусом.
Глобальный `Handler.php` не является частью контракта ошибок V1 API.

---

## 6. Bearer-токен: хранение на фронте и срок жизни

**Решение**: Pinia store в памяти + `localStorage` для сохранения авторизации между
вкладками. XSS-защита обязательна, а серверный TTL ограничивает срок действия токена
одним днём.

```ts
// stores/auth.ts
export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const user = ref<AuthUser | null>(null)

  async function login(email: string, password: string) {
    const { data } = await apiClient.post('/api/v1/auth/login', { email, password })
    token.value = data.data.token
    localStorage.setItem('auth_token', token.value)
  }

  async function logout() {
    await apiClient.delete('/api/v1/auth/logout')
    token.value = null
    user.value = null
    localStorage.removeItem('auth_token')
  }

  const isAuthenticated = computed(() => !!token.value)
  return { token, user, login, logout, isAuthenticated }
})
```

**Axios interceptor**: добавляет `Authorization: Bearer {token}` и перехватывает 401
→ очищает store/localStorage и вызывает `router.push('/app/login')`. Автоматического
обновления токена нет.

---

## 7. ApiV1TestCase: паттерн PHPUnit базового класса

**Решение**: Абстрактный класс в `Tests\Feature\Api\V1\ApiV1TestCase` с хелперами.

```php
abstract class ApiV1TestCase extends TestCase
{
    use RefreshDatabase;

    protected function withToken(User $user): static
    {
        $token = $user->createToken('test')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    protected function assertDirectCollection(array $response): void
    {
        $this->assertIsList($response);
    }

    protected function assertDirectResource(array $response): void
    {
        $this->assertIsArray($response);
        $this->assertArrayNotHasKey('data', $response);
    }

    protected function assertErrorEnvelope(array $response): void
    {
        $this->assertArrayHasKey('errors', $response);
    }
}
```

---

## 8. PrimeVue 4 + TypeScript интеграция

**Решение**: Зарегистрировать PrimeVue глобально в `main.ts` с темой Aura.
Импортировать только нужные компоненты (tree-shaking).

```ts
// main.ts
import PrimeVue from 'primevue/config'
import Aura from '@primeuix/themes/aura'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
// ...

app.use(PrimeVue, { theme: { preset: Aura } })
app.component('DataTable', DataTable)
app.component('Column', Column)
```

**PrimeVue 4 особенность**: стили инжектируются через CSS variables, нет
необходимости в отдельных CSS-импортах компонентов — только `@primevue/themes`.

---

## 9. ESLint + Prettier конфигурация для Vue 3 + TypeScript

**Решение**: `eslint.config.js` (flat config, современный стандарт ESLint 9+).

```js
// eslint.config.js
import pluginVue from 'eslint-plugin-vue'
import tseslint from 'typescript-eslint'

export default [
  ...tseslint.configs.recommended,
  ...pluginVue.configs['flat/recommended'],
  { rules: { 'vue/multi-word-component-names': 'off' } }
]
```

**Prettier**: `.prettierrc.json` со стандартными настройками (semi: true, singleQuote: true).
`prettier` интегрируется с ESLint через `eslint-config-prettier`.

---

## 10. Dockerfile: добавление Node.js build step

**Решение**: Многоэтапная сборка — первый stage строит фронт, второй — продакшн PHP образ.

```dockerfile
# Stage 1: frontend build
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources/spa ./resources/spa
COPY vite.config.ts tsconfig.json ./
RUN npm run build:spa

# Stage 2: PHP app (существующий Dockerfile)
FROM php:8.5-fpm
# ... (существующее содержимое) ...
COPY --from=frontend /app/public/spa ./public/spa
```

**npm скрипт**: `"build:spa": "vite build"` добавляется в `package.json`.
Старые скрипты (`development`, `production` через laravel-mix) сохраняются без изменений.
