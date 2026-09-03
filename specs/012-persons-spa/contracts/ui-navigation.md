# UI Contract: Persons SPA navigation

## Canonical routes

| Purpose | URL | Navigation mode |
|---|---|---|
| Persons listing | `/app/persons` | Vue Router |
| Person details | `/persons/{id}/show` | Full-page legacy href |
| Create person | `/persons/create` | Full-page legacy href, authenticated only |
| Edit person | `/persons/{id}/edit` | Full-page legacy href, authenticated only |
| Delete person | `/persons/{id}/delete` | Full-page legacy href, authenticated only |
| Removed listing | `/persons` | 404 |

The SPA navbar persons entry and all listing links point to `/app/persons`. Person names retain
legacy detail hrefs. Club links from person rows use `/app/clubs/{clubId}` when a club exists.

## Shared table

`PersonTable` is rendered by both `PersonsPage` and `ClubDetailsPage`. It displays the same name,
club, birth-year, rank and authenticated action columns. Missing club/birth-year values are
empty text and never produce an invalid anchor.

## Filtering and states

- Name input is trimmed, debounced by 300 ms, and submits only at least 3 non-space characters.
- Rank, birth-year and club changes are applied cumulatively and reset pagination to page 1.
- Birth-year options include “any year” plus every year from 1920 through the current year.
- Loading, validation, empty-result, and retryable API-error states are visible.
- A newer request always wins; an older in-flight response cannot replace current rows or pagination.

## Authorization

Guests may load the table and person detail links. The create action and edit/delete actions are
not rendered for guests. Authenticated users use the existing legacy create/edit/delete routes; no
new authorization policy is introduced.
