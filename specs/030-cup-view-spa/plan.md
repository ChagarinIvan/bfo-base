# Implementation Plan: Cup View SPA

**Branch**: `030-cup-view-spa` | **Date**: 2026-09-19 | **Spec**: [spec.md](spec.md)

## Summary

Replace the Blade cup-show page with `/app/cups/:cupId`. Reuse the current cup
detail DTO and cup-event DTO, expose a paginated cup-event V1 read endpoint,
render the cup card and filtered stage table with shared SPA components, and
remove only the superseded show route/controller/template.

## Technical Context

**Language/Version**: PHP 8.5, Laravel 13, TypeScript, Vue 3

**Primary Dependencies**: PrimeVue, Axios, Pagerfanta/Slice, PHPUnit, Vitest

**Storage**: MySQL/Eloquent; no schema migration

**Testing**: PHPUnit API and Application tests; Vitest API/page/router tests

**Target Platform**: Laravel monolith with `/app` SPA

**Project Type**: Web application

**Performance Goals**: A paginated cup-event request reads one bounded page
without event or competition relations; the SPA resolves its bounded event IDs
through the existing events endpoint. Default page size is 50.

**Constraints**: Public responses omit impressions; controls render only from
the existing authenticated SPA state; V1 uses DTO validation and Slice headers;
no new repository or legacy service is created.

**Scale/Scope**: One detail route, one paginated read endpoint, one existing
Application query refactor, and retirement of one Blade rendering surface.

## Constitution Check

- The V1 action accepts `CupEventSearchDto` and `Pagination`, creates a command,
  and returns `Slice<ViewCupEventDto>` through `ApiAction`.
- The Application service retains mapping in the existing assembler; the Domain
  port gains `paginate(Criteria)` and the Eloquent adapter owns the query.
- `ViewCupEventDto` impressions receive authenticated serialization groups, as
  `ViewCupDto` already does.
- API request tests use real records and class-level `@see`; Application tests
  mock collaborators and assert criteria/slice mapping.
- No new `app/Services`, facades, manual JSON, unbounded list, or N+1 relation
  access is introduced.

**Result**: PASS before research and after design.

## Design

1. Move the existing `GET /api/v1/cups/{cupId}` detail action into optional
   authentication so a guest can render the card while its annotated
   impressions remain private.
2. Add optional-authentication `GET /api/v1/cups/{cupId}/events` using the
   existing `CupEventSearchDto`, `ListCupEvent`, `ListCupEventService`,
   `CupEventAssembler`, and `ViewCupEventDto`. Extend search with optional
   date/name/eventIds filters, constrain `cupId` from the route, and let shared
   `Pagination` supply page/perPage headers.
3. Change the existing cup-event Domain port and Eloquent adapter from an
   unbounded `byCriteria()->all()` read to `paginate(Criteria)`. The adapter
   filters event IDs, event date, and normalized event/competition title, and
   orders by event ID then stage ID. It does not eager-load relations for the
   compact DTO.
4. Add authenticated serialization groups to `ViewCupEventDto` impressions.
   The action stays public; `ApiAction` controls group selection from the
   optional Bearer context.
5. Add SPA cup-event types/client and `CupViewPage` using `Card`,
   `ListingTable`, `FilterPanel`, `DatePicker`, `InputText`,
   `ImpressionDetails`, `ActionButton`, and `ConfirmDeleteDialog`. It defaults
   to 50 rows, debounces valid name searches, resets page on either filter,
   and uses existing legacy endpoints only for unported operations.
6. Register `/app/cups/:cupId`, make cup list links SPA links, and show the
   card/table management controls only to authenticated users. The public
   stage name links to `/app/events/:eventId`.
7. Delete `ShowCupAction`, its `GET /cups/{cupId}/show` route, its Blade
   template and uniquely obsolete tests. Update retained legacy mutations and
   cache clear redirects to `/app/cups/{cupId}`; cup deletion returns to the
   SPA list.

## Project Structure

```text
app/
├── Application/{Dto,CupEvent}/ and Service/CupEvent/
├── Bridge/Laravel/Http/Controllers/Api/V1/Cup/
├── Bridge/Laravel/{Http/Controllers/{Cup,CupEvents},Provider}/
├── Domain/Cup/CupEvent/
└── Infrastructure/Laravel/Eloquent/CupEvent/
resources/spa/
├── api/{cups,types}.ts
├── pages/cups/{CupViewPage,CupsPage,cupViewModels}.{vue,test.ts,ts}
└── router/{index,index.test}.ts
tests/
├── Application/Service/CupEvent/
├── Feature/Api/V1/Cup/
└── Bridge/Laravel/Http/Controllers/{Cup,CupEvents}/
specs/030-cup-view-spa/
```

**Structure Decision**: Extend the existing Cup/CupEvent target query and SPA
directories in place. Legacy mutation/form actions remain because their forms
and operations are explicitly outside this migration.

## Complexity Tracking

No constitution violations or additional complexity require justification.
