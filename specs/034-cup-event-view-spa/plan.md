# Implementation Plan: Cup Event View SPA

**Branch**: `034-cup-event-view-spa` | **Date**: 2026-09-20 | **Spec**: [spec.md](spec.md)

## Summary

Replace the legacy group-specific standings render with `/app/cup-events/:cupEventId`.
Reuse the existing public cup-event, cup, and event reads for the detail card,
and add a group-scoped, paginated points read. The SPA uses the shared
detail-card, filter, select, and listing-table components. Each asynchronous
read supports cancellation and ignores obsolete completions. Cup-stage links
point to the new route. Only the retired rendering path and its uniquely unused
legacy support are deleted.

## Technical Context

**Language/Version**: PHP 8.5, Laravel 13, TypeScript, Vue 3.

**Primary Dependencies**: PrimeVue, Axios, Pagerfanta/Slice, PHPUnit 13,
Vitest.

**Storage**: MySQL 8.4 through existing Eloquent infrastructure; no schema
migration.

**Testing**: PHPUnit Application/API/route tests and Vitest API/page/router
tests.

**Target Platform**: Laravel monolith, public `/api/v1` and `/app` SPA.

**Project Type**: Web application.

**Performance Goals**: The visible standings response is bounded to a maximum
of 50 rows by default and loads card/row relationships in bounded batches with
no per-row database or HTTP query.

**Constraints**: V1 camelCase DTOs and shared serializer; public detail read;
Belarusian SPA text only; calculated standings preserve legacy scoring; no
new `app/Services` or `app/Repositories` code.

**Scale/Scope**: One SPA route/page, two public V1 reads, one cup-table link
fix, and retirement of one Blade surface.

## Constitution Check

- New reads use narrow V1 actions, command-only Application services, DTO
  assemblers, Domain query ports and Infrastructure Eloquent adapters.
- The standings calculation stays a legacy-dependent domain capability. Its
  full-result calculation is required to determine relative points; the new
  response boundary still returns a `Slice` and must document/query-test that
  only the requested DTO page and its references are serialized. This is a
  contained compatibility seam to be removed only with a separate scoring
  refactor.
- API request tests use real database records and class-level `@see`; pure
  Application tests mock ports and assemblers.
- The SPA uses existing shared components and Belarusian translation keys.
- No facade, manual JSON response, new legacy service, or client-side N+1 is
  introduced. The legacy action/template and obsolete service usages are
  removed after route replacement is verified.

**Result**: PASS with the explicitly documented scoring-calculation seam.

## Design

1. Introduce a public cup-event context query based on `cupEventId`. It reads
   the active cup event with its active cup and linked event/competition, then
   assembles public card data and eligible cup groups. It does not require a
   redundant cup identifier in the URL.
2. Extract calculated standings into a named Application read use case. It
   validates that the selected `CupGroup` belongs to the cup, asks the existing
   cup-type calculation for group points, applies normalized athlete-name
   filtering, and returns a `Slice` of `ViewCupEventPointDto` rows. The
   calculator remains scoring authority; `Slice` constrains response delivery.
3. Reuse `GET /api/v1/cup-events/{cupEventId}`, `GET /api/v1/cups/{cupId}` and
   the existing event lookup for the card. Add
   `GET /api/v1/cup-events/{cupEventId}/points`, which accepts
   `groupId`, optional `name`, and shared `page`/`perPage`; invalid group or
   inactive cup event becomes the existing application 404, while invalid
   query input returns standard 422.
4. Add typed SPA client functions and `CupEventViewPage`. It loads the related
   card resources, preselects the first group, reloads points at 50 rows,
   debounces valid athlete-name input, resets page on every filter change, and
   uses the shared not-found flow. It aborts obsolete Axios requests and clears
   stale points before presenting an error. Card links target existing
   competition/event SPA routes.
5. Register `/app/cup-events/:cupEventId`; make the stage-name link in
   `CupViewPage` target this route. Keep explicit event navigation separately
   available from the card.
6. Remove `ShowCupEventGroupAction`, its `{cup}/{event}/{group}/show` web
   route/import, Blade template, obsolete test class and imports. Search every
   `app/Services` use made solely for the page and delete only zero-usage code;
   cup table/export/cache paths remain unchanged.

## Project Structure

```text
app/
├── Application/{Dto/CupEvent,Service/CupEvent}/
├── Bridge/Laravel/{Http/Controllers/Api/V1/Cup,Provider}/
├── Domain/Cup/CupEvent/
└── Infrastructure/Laravel/Eloquent/CupEvent/
resources/
├── lang/by.json
└── spa/
    ├── api/{cups,types}.ts
    ├── pages/cups/{CupViewPage,CupEventViewPage,cupEventViewModels}.{vue,test.ts,ts}
    └── router/{index,index.test}.ts
tests/
├── Application/Service/CupEvent/
├── Feature/Api/V1/Cup/
└── Feature/Cup/
specs/034-cup-event-view-spa/
```

**Structure Decision**: Extend the existing CupEvent target layers and SPA cup
directory. The old controller was a rendering adapter, so it is removed rather
than adapted. No new legacy class is added.

## Complexity Tracking

| Compatibility seam | Why needed | Refactor deferred because |
|---|---|---|
| Full group calculation precedes response slicing | Existing cup formulas require the complete group result to establish relative points and ranking. | Replacing every CupType calculation with SQL/window-backed scoring would change a larger scoring domain beyond this UI migration. |
