# Quickstart validation

## Prerequisites

- PHP dependencies installed and the configured application test environment available.
- Node dependencies installed for the SPA.

## Automated checks

Run focused checks while implementing:

```bash
php -d xdebug.mode=off vendor/bin/phpunit tests/Feature/Competition
npm run typecheck
npm run test -- --run
```

At feature completion, run the repository quality gates required by the constitution:

```bash
composer cs
composer stan
composer rector -- --dry-run
composer test
npm run ci
```

## Manual scenarios

1. Open `/app/competitions` anonymously and verify the SPA list loads.
2. Open a competition details page, then create/edit as an authenticated user; verify the URL
   remains under `/app/competitions` and the existing API flow works.
3. Request every legacy path listed in [removal-contract.md](contracts/removal-contract.md) and
   verify no old Blade page renders and no mutation occurs.
4. Open `/`, the navbar, the 404 fallback and login/registration completion; verify competition
   destinations use the SPA list.
5. Follow competition links from events, groups, persons, flags and cups; verify they open the SPA
   details page without breaking the source page.
6. Search the repository for removed competition actions, views and legacy web URLs; verify only
   documented API paths, canonical SPA paths and intentional absence assertions remain.
7. Refresh each `/app/competitions*` route directly and verify the SPA shell loads.
