# Competition legacy removal contract

## Canonical SPA paths

| User capability | Canonical path | Access |
|---|---|---|
| List competitions | `/app/competitions` | Public |
| View a competition | `/app/competitions/{competitionId}` | Public |
| Create a competition | `/app/competitions/create` | Authenticated |
| Edit a competition | `/app/competitions/{competitionId}/edit` | Authenticated |

## Required absence

The following legacy web route families MUST not be registered or execute a mutation:

- `/competitions`
- `/competitions/{id}/show`
- `/competitions/create`
- `/competitions/{id}/edit`
- `/competitions/store`
- `/competitions/{id}/update`
- `/competitions/{id}/delete`

The exact method combinations are determined from the preflight route inventory. The old actions,
Blade views and tests that exist solely for these routes are removed together.

## Preserved behavior

- `/api/v1/competitions...` remains the data interface for the SPA.
- Event/protocol/cup links continue to open their existing functionality.
- Internal competition links in retained sections target `/app/competitions...`.
- Authentication and authorization behavior is unchanged.

## Verification result (2026-09-03)

- Route inventory shows only the five intended `/api/v1/competitions` routes.
- The legacy web route families above are not registered.
- Competition navigation regression tests pass, including absence of legacy presentation files and
  the removed controller import.
