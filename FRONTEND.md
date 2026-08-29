# SPA frontend

The new frontend is a Belarusian Vue 3 application under `resources/spa/`. It
uses Vite, TypeScript, Vue Router, Pinia, Axios and PrimeVue. The legacy Blade
frontend remains under `resources/js/` and is built separately by laravel-mix.

## Local development

Use Node.js 22 and npm 10 or newer (`.nvmrc` contains `22`):

```bash
npm ci
npm run dev:spa
```

Run Laravel separately on port 8000. Vite serves the SPA at
`http://localhost:5173/app/competitions` and proxies `/api` to Laravel.

Quality checks:

```bash
npm run lint
npm run typecheck
npm run test
npm run build:spa
```

## Structure

- `resources/spa/pages/` — route pages;
- `resources/spa/components/` — shared Vue components;
- `resources/spa/router/` — `/app/*` routes and auth guards;
- `resources/spa/stores/` — Pinia state;
- `resources/spa/api/` — typed API client and envelopes;
- `resources/spa/i18n.ts` — typed access to `resources/lang/by.json`.

Do not put user-facing text directly in templates. Add a Belarusian key to
`resources/lang/by.json` and use `t('spa.some.key')`.

## Adding a page

Create `resources/spa/pages/HelloPage.vue`, using `t()` for all visible text,
then register it in `resources/spa/router/index.ts`:

```ts
import HelloPage from '../pages/HelloPage.vue'

{ path: '/app/hello', component: HelloPage }
```

Use `meta: { requiresAuth: true }` for private pages. API calls go through
`resources/spa/api/client.ts`; it adds the Bearer token and redirects to login
after a 401 response.
