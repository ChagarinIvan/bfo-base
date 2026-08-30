# UI contract: SPA competition routes and hybrid navbar

## SPA routes

| Route | Access | Page |
|---|---|---|
| `/app/competitions` | Public | List with year, name and date filters. |
| `/app/competitions/create` | Authenticated | Create form. |
| `/app/competitions/{id}` | Public | Competition details and events table. |
| `/app/competitions/{id}/edit` | Authenticated | Prefilled edit form. |

The list name link uses the SPA detail route. Event, flag, cup and other unmigrated functions use existing legacy URLs.

## Navbar

| Visibility | Menu | Item | Destination |
|---|---|---|---|
| Public | Competitions | Competitions | SPA list: `/app/competitions`. |
| Public | Competitions | Cups | Existing legacy cups list. |
| Public | Persons | Persons | Existing legacy persons list. |
| Public | Persons | Clubs | Existing legacy clubs list. |
| Public | Persons | Ranks | Existing legacy ranks list. |
| Public | Account | Login | SPA login. |
| Authenticated | Competitions | Groups | Existing legacy groups list. |
| Authenticated | Help | FAQ | Existing legacy FAQ. |
| Authenticated | Help | API | Existing legacy API FAQ. |
| Authenticated | Account | Registration | Existing legacy registration page. |
| Authenticated | Account | Logout | Revokes SPA token. |

Auth-only entries are absent for visitors. Legacy entries are ordinary `href` values, not nonexistent Vue routes.

## Shared interaction rules

- Actions appear only for authenticated users and contain edit plus delete.
- Delete dialog includes selected competition name; only confirm sends DELETE.
- Common action button, menu and confirmation primitives are reused by competition pages.
- Create and edit use one form component; validation messages render near relevant fields.
