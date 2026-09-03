# Quickstart validation

## Automated checks

```bash
php -d xdebug.mode=off vendor/bin/phpunit tests/Bridge/Laravel/Http/Controllers/Competition
npm run typecheck
npm run test
composer cs
```

Run the complete relevant suites after legacy deletion; remove old tests only with their behavior.

## Manual scenarios

1. Open `/app/competitions` anonymously, open a competition, and verify the URL stays under `/app`.
2. Authenticated: open create and edit, submit each form, verify V1 API call and return navigation.
3. Open `/`, 404 and complete login/registration; verify competition links target SPA list.
4. Refresh each `/app/competitions*` URL and verify SPA shell loads without 404.
5. Follow event/protocol links and verify their existing pages still work.
6. Search for removed actions/views and confirm only documented redirects or historical references.
