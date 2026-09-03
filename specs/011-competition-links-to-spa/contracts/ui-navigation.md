# UI navigation contract

## Canonical competition URLs

- Public list: `GET /app/competitions`
- Public details: `GET /app/competitions/{competitionId}`
- Authenticated create: `GET /app/competitions/create`
- Authenticated edit: `GET /app/competitions/{competitionId}/edit`

## Link rules

1. Internal navigation to these four screens MUST use canonical SPA paths.
2. Competition links to events/protocols MAY use existing Blade URLs until those features migrate.
3. Unauthenticated create/edit access MUST resolve to `/app/login` through the SPA guard.
4. No legacy competition web URLs are retained; `/competitions`, `/competitions/{id}/show`,
   `/competitions/create`, `/competitions/{id}/edit` and legacy mutation URLs are not registered.

## Regression surface

Cover navbar, root, 404, login/registration completion, competition list/detail links, create/edit
actions and post-delete redirects where applicable.
