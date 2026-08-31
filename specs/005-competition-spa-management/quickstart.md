# Quickstart: Validation of SPA competition management

**Feature**: `005-competition-spa-management`

## Prerequisites

1. Install locked dependencies: `composer install` and `npm ci`.
2. Configure MySQL and run `php artisan migrate`.
3. Create an active user and competitions for at least one year. One competition should have several active events and protocol lines.
4. Build/run SPA with `npm run build:spa` or `npm run dev:spa`.

Exact contracts:

- [competition management](contracts/api-competition-management.md)
- [events](contracts/api-events.md)
- [SPA routes and navbar](contracts/ui-navigation.md)

All V1 query parameters and JSON request fields use `camelCase`; for example, use `perPage` and
`competitionId`, never `per_page` or `competition_id`.

## Scenario 1: Public filtering

1. Open `/app/competitions` without login.
2. Select year, enter a three-character name fragment, wait for update, then select a date.
3. Check a date equal to `from`, a date inside a multi-day competition and a date equal to `to`.
4. Combine filters and change one while on a later page.

Expected: only matching records appear; all inclusive boundaries match; pagination resets to an existing page; one/two character name input gives a hint and sends no name search.

## Scenario 2: Public competition details

1. Open a competition name from SPA list.
2. Confirm its SPA detail page has competition data and an events table.
3. Open the event link from the table.
4. Open a competition with no events and a nonexistent id.

Expected: the event link goes to a working legacy page; empty and missing states are understandable;
no hidden browser console/network error.

## Scenario 3: Authenticated update and soft delete

1. Login through `/app/login`; open `/app/competitions`.
2. Use row actions, edit a prefilled competition, save and confirm the change in list/detail page.
3. Open delete for another competition, close it, reopen it and confirm.
4. Refresh list and open deleted competition detail URL.

Expected: actions are auth-only; closing dialog changes nothing; confirmation removes the competition from active list/detail but retains database record.

## Scenario 4: Authorization and navigation

1. Logout and request PUT/DELETE without Bearer token.
2. Inspect navbar before and after login.
3. Open every navigation item.

Expected: protected requests return standard unauthenticated response; auth-only links are hidden for visitors; competition routes stay SPA and other items reach existing pages.

## Automated validation

During implementation run focused checks:

```bash
vendor/bin/phpunit tests/Feature/Api/V1/Competition tests/Feature/Api/V1/Event
npm run ci
composer cs
composer stan
```

At feature completion or after a large integration phase:

```bash
composer rector -- --dry-run
composer test
```

Expected: commands exit with code 0. Request tests cover filter combinations, validation, authorization, soft-delete and N+1; frontend tests cover debounce, routes, shared form and confirmation.
