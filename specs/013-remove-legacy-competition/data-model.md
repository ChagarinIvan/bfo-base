# Data model and migration boundaries

This feature changes no persisted data and adds no entities, fields, relationships or migrations.

## Retained data

- **Competition**: existing competition records and their active/inactive lifecycle remain intact.
- **Event and protocol data**: existing records remain accessible from retained pages and SPA links.
- **V1 API contracts**: existing list, view, create, update and delete payloads and authorization
  behavior are unchanged.

## Presentation entities

- **Canonical SPA route**: a user-facing path under `/app/competitions...` for the existing list,
  details or authenticated form screens.
- **Legacy entry point**: an old web route, controller action, Blade view, import or test whose only
  purpose is the removed competition presentation/mutation flow.

## Invariants

- Removing a legacy entry point never deletes a Competition or related Event record.
- API/domain/Application competition code remains reachable by the SPA.
- Links from retained sections resolve to the canonical competition SPA or to their own existing
  event/protocol functionality.

## Implementation result

- No database schema, migration, entity, API payload or persistence code was changed.
- The legacy presentation directories were absent after cleanup; retained API, Application and
  Domain competition code remains present for the SPA.
