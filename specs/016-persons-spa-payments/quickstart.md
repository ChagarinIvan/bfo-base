# Quickstart: SPA-оплаты персоны

## Предварительные условия

- PHP 8.5/Laravel 13 dependencies installed.
- MySQL test database configured through .env.testing.
- Node dependencies installed.

## Backend

    php artisan test tests/Feature/Api/V1/PersonPayment tests/Application/Service/PersonPayment

Ожидается: authenticated paginated/year-filtered list, empty state, 401 для list и mutation без auth,
422 для invalid date, create/update по person/year и shared payment consumer
regressions.

## Frontend

    npm run test -- resources/spa/pages/persons/PersonPaymentsPage.test.ts resources/spa/pages/persons/PersonPaymentForm.test.ts resources/spa/api/personPayments.test.ts

Ожидается: deep link, loading/error/empty/not-found states, anonymous action
visibility, date validation retention, pending duplicate-submit protection and
successful return to the list.

## Final checks

    composer test
    npm run ci
    composer cs
    composer stan
    composer rector
    git diff --check

Additionally inspect php artisan route:list and usages: payment-only Blade
routes/actions/views are removed, while persons_payments protocol/rank queries
remain available.
