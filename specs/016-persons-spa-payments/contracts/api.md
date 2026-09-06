# API Contract: SPA-оплаты персоны

Base path: /api/v1

## List person payments

GET /persons/payments?personId={personId}&year={year}&page={page}&perPage={perPage}

Middleware: required API authentication.

Success 200 (pagination metadata is returned in `X-Pagination-*` headers):

    [
      {
        "id": "101",
        "personId": "3531",
        "year": "2025",
        "date": "2025-03-14",
        "created": {"at": "2025-03-14T10:00:00+00:00", "by": "admin"},
        "updated": {"at": "2025-03-14T10:00:00+00:00", "by": "admin"}
      }
    ]

Ordering is the existing payment repository order (id DESC). The endpoint
returns an empty array for an existing person without payments.

Errors:

- an unavailable or unknown person produces an empty paginated list;
- standard API validation error contract for a missing or malformed `personId`.

## Create or update person payment

POST /persons/payments

Middleware: required API authentication.

Request:

    {"personId":"3531", "date":"2025-03-14"}

Success 201 for a new payment and 200 for an update or idempotent existing
payment:

    {
      "id": "101",
      "personId": "3531",
      "year": "2025",
      "date": "2025-03-14",
      "created": {"at": "2025-03-14T10:00:00+00:00", "by": "admin"},
      "updated": {"at": "2025-03-14T10:00:00+00:00", "by": "admin"}
    }

Validation 422:

    {"errors":[{"code":"validation","field":"date","message":"..."}]}

The response preserves the existing API validation format. Unauthenticated
requests return 401; unknown people return 404.

## SPA route contract

- /app/persons/{personId}/payments renders the list and add action for an authenticated user.
- /app/persons/{personId}/payments/create renders the date form for an
  authenticated user.
- Anonymous users are redirected by the SPA auth guard when opening either route.
