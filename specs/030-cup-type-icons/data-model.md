# Data Model: Cup Type Icons

## Cup type icon definition

Frontend-only immutable definition:

- `type`: backend enum string
- `icon`: bundled Font Awesome class name
- `label`: Belarusian translation key for the type
- `fallback`: whether the definition is the neutral unknown-type entry

## Mapping behavior

- All current backend enum values have explicit definitions.
- Lookup accepts an arbitrary string and returns a definition.
- Unknown strings return the neutral fallback definition.
- The lookup never transforms or replaces the original cup `type` sent in an
  API form payload.

## No persistence changes

No database table, migration, API DTO, or backend enum changes are required.
