# Data Model: SPA night mode

## Appearance preference

| Field | Type | Values | Visibility |
|---|---|---|---|
| mode | string | `light`, `night` | Browser-local |

The storage key is an SPA implementation detail and must be versioned or validated so unknown values fall back to `light`.

## Appearance state

The runtime state contains:

- the active mode;
- a command to toggle the mode;
- safe initialization from browser storage;
- synchronization of the document theme marker and persisted value.

No server-side relationship exists. Appearance state must not be included in API requests or authentication state.
