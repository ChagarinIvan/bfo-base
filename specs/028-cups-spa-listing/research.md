# Research: SPA-листинг кубков

## Decisions

### Reuse optional authentication and existing listing conventions

- Decision: register the read endpoint in the optional-auth API group and derive the
  visibility mode from the authenticated request.
- Rationale: this is the established pattern for public SPA listings and prevents an
  anonymous client from selecting administrative data.

### Keep listing transport separate from detail DTOs

- Decision: introduce a narrow listing search DTO, command, criteria mapping and view DTO;
  keep `ViewCupDto`/`CupAssembler` for detail and mutation flows until those flows migrate.
- Rationale: the current assembler loads detail-oriented data and couples listing to legacy
  use cases. A separate read model makes the listing payload explicit and deletable.

### Preserve existing cup URLs during the incremental migration

- Decision: SPA rows link to existing `/cups/{id}/show` and `/cups/{cup}/{group}/table`
  URLs; only the listing entrypoint moves to `/app/cups`.
- Rationale: detail/table migration is outside this feature and existing workflows must not
  be broken.

### Use standard `Slice` pagination

- Decision: list service returns `Slice<ViewCupDto>` and the API action delegates
  pagination headers to `ApiAction`.
- Rationale: it matches current competition, event and person SPA APIs.

## Existing patterns inspected

- `ListCompetitionsAction` and `ListCompetitionsService` for paginated API flow.
- `SearchClubDto`/`SearchCompetitionDto` for normalized search DTOs.
- `ListingTable.vue`, `YearFilter.vue`, `FilterPanel.vue` for SPA listing UI.
- `OptionalAuthenticateApiV1` for public/authenticated visibility boundaries.
