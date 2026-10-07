# Quickstart: История обновлений сайта

1. Sign in and open `/app/updates` from the top navigation. Confirm latest date first, short rows and working disclosure via mouse and keyboard.
2. Open an active feature and follow its link to a real SPA listing. Open a replaced or parser entry and confirm its explanation stands alone without a GitHub link or artificial example.
3. Sign out. The menu item disappears. Open `/app/updates` directly and confirm login with `return=/app/updates`.
4. Compare `specs/*/spec.md` directories to `updates.ts`: one record per directory, including 041 and both variants of 030/034.
5. Run focused Vitest checks during implementation; at feature end run `npm run ci`, `composer cs`, `composer stan`, `composer rector -- --dry-run`, and `composer test`. Confirm normal SPA startup and no new request path/N+1.
