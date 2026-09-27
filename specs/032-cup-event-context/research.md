# Research: Cup Event Context Badges

## Decisions

- **Batch endpoint**: use `GET /api/v1/cup-events?eventIds[]=…` rather than
  extending each event payload. It supports all existing table sources and
  keeps cup context optional.
- **Active scope**: reuse `CupEventRepository` active cup/stage query and
  eager-load only `cup`; no events or competitions are fetched for this read.
- **Navigation**: use the first cup group, matching current cup table links,
  because the existing legacy stage view requires a group route segment.
- **Badge UI**: reuse `CupTypeIcon` and PrimeVue `Popover`; a shared component
  prevents four copies of icon/link/popover markup.
