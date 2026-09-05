# Quickstart: SPA-управление персональными промптами

## Предварительные условия

- PHP 8.5/Laravel 13 dependencies installed.
- MySQL test database configured through `.env.testing`.
- Node dependencies installed.

## Проверка backend

```bash
php artisan test tests/Feature/Api/V1/PersonPrompt tests/Application/Service/PersonPrompt
```

Ожидается: authenticated paginated list, 404 для unknown person/prompt, 401 для запросов без auth, 422 без
потери validation input, create/update/delete success и regression coverage для shared prompt consumers.

## Проверка frontend

```bash
npm run test -- resources/spa/pages/personPrompts resources/spa/api/personPrompts.test.ts resources/spa/router/index.test.ts
```

Ожидается: deep link `/app/persons/{personId}/prompts`, loading/error/empty states, pagination,
auth-aware actions, form errors, duplicate-submit protection, delete confirmation и отсутствие club
column на странице клуба.

## Финальная проверка

```bash
composer test
npm run ci
composer cs
composer stan
composer rector
git diff --check
```

Дополнительно проверить `php artisan route:list` и поиск usages: prompt-only Blade routes/actions/views
удалены, а shared parser/import/rank consumers остались рабочими.
