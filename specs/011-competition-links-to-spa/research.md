# Research: competition links and legacy entry points

## Findings

- SPA routes already exist: `/app/competitions`, `/app/competitions/{id}`,
  `/app/competitions/create`, `/app/competitions/{id}/edit`.
- `WebRoutesServiceProvider` registers Blade list, show, create, edit, store, update and delete
  routes under `/competitions`.
- Navbar, 404, login/registration completion and competition Blade views still reference legacy
  actions or URLs.
- SPA pages already use V1 endpoints and router auth guards; no new API contract is required.
- Legacy web store/update/delete actions had no remaining internal consumers; existing V1 mutation
  endpoints cover these scenarios.
- Event/protocol links in competition details are outside this migration and remain server URLs.

## Decisions

### SPA paths are canonical internal competition links

The target pages and auth behavior already exist; keeping Blade links creates two UIs and bypasses
SPA navigation. Keeping Blade list as a compatibility shell was rejected.

### Remove redundant Blade code as one unit

An action, route, view and test are removed together only after usages are inventoried. This avoids
dead entry points and protects non-migrated flows.

### Preserve redirects only for real external links

An old URL may redirect to SPA to protect bookmarks without retaining a Blade page. Exact redirects
are decided during implementation after searching all usages.

## Implementation check

Search PHP, Blade, TypeScript, tests and documentation for every legacy action before deletion and
classify each usage as removable, redirect-compatible or retained transition behavior.

## Implementation result (2026-09-03)

- Removed the four competition presentation actions (`ShowCompetitionsListAction`,
  `ShowCompetitionAction`, `ShowCreateCompetitionFormAction`, `ShowEditCompetitionFormAction`),
  their four Blade views, GET web routes and dedicated tests.
- Updated navbar, root, 404, login/registration redirects, competition mutation redirects and
  links from events, persons, groups, flags and cups to `/app/competitions...`.
- Removed the legacy store/update/delete POST/GET actions, web routes and tests as well: SPA uses
  the existing V1 create/update/delete endpoints, and repository inventory found no remaining
  internal consumers of the old mutation routes.
- No remaining production references to the removed presentation actions or `/competitions/*/show`
  were found. The old URLs are intentionally 404 rather than compatibility redirects.
