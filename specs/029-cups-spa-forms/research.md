# Research: SPA Cup Forms and Legacy Route Removal

## Existing backend behavior

- `CupDto` already defines the canonical editable fields and validation rules: `name`, `eventsCount`,
  `year`, `type`, and `visible`.
- `AddCup`/`AddCupService` and `UpdateCup`/`UpdateCupService` already implement domain creation and
  update behavior, including authenticated impression data. The new API layer should delegate to them.
- `ViewCupService` and `CupAssembler` already provide the full editable view DTO, including authenticated
  audit fields.
- Existing API routes use `POST` for create, `GET /{id}` for view, and `PUT /{id}` for update under
  authenticated Sanctum middleware.

## Existing frontend behavior

- Competition and person forms provide the established pattern for PrimeVue form controls, pending state,
  field error mapping, toast success notifications, and guarded SPA routes.
- The cups listing currently links to legacy routes for detail/table operations. Only the edit action and
  create button move to `/app/cups/*`; unrelated legacy links remain ordinary browser links.
- SPA imports `resources/lang/by.json`; no Russian SPA translation is added.

## Legacy route boundary

- Remove `ShowCreateCupFormAction`, `ShowEditCupFormAction`, `StoreCupAction`, `UpdateCupAction`, their
  Blade views, and only the corresponding create/edit/store/update web route registrations.
- Preserve `ShowCupAction`, `ShowCupTableAction`, cup event routes, exports, and `DeleteCupAction`.
- Add regression assertions for unavailable legacy form/mutation URLs and preserved unrelated routes.

## Decisions

1. Reuse existing Application services and validation instead of creating a parallel SPA-specific domain flow.
2. Use `/app/cups/create` and `/app/cups/:id/edit` for SPA routes, matching the existing app shell.
3. Use `/api/v1/cups`, `/api/v1/cups/{cupId}`, and authenticated V1 conventions for transport.
