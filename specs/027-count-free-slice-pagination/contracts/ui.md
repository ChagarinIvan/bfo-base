# SPA Contract: Count-Free Slice Pagination

## Pagination state

SPA listing state contains:

- `currentPage`;
- `perPage`;
- `hasNext`;
- current page items and loading/error state.

It does not require `total` or `lastPage`.

## Navigation

- Next-page control is enabled only when `hasNext` is true.
- Previous-page control is enabled when `currentPage > 1`.
- Changing a filter resets the page to 1 and replaces pagination state from the new response headers.
- Changing page size resets the page to 1 unless the existing component has a stronger established rule.
- Empty and loading states remain explicit; an empty page does not imply an API error.

## Compatibility boundary

Shared pagination models/components and every paginated page must stop reading `total`/`lastPage`. Components must not synthesize a total or last page from the current response. Listing table column, DTO, auth and filter behavior remains unchanged.
