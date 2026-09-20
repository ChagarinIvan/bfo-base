# Quickstart: Cup Event View SPA

## Prerequisites

- Local MySQL and application dependencies are available.
- A cup event exists with at least one eligible cup group and calculated
  protocol results.

## Automated validation

Run focused backend tests for the new V1 context/standings reads and route
retirement, then the CupEvent SPA API, page, and router tests. At feature end,
run `composer cs`, `composer stan`, `composer rector`, `composer test`, and
`npm run ci` once.

## Manual flow

1. Open `/app/cups/{cupId}` and select a stage name.
2. Confirm its destination is `/app/cup-events/{cupEventId}`.
3. Confirm the information card names the cup, competition, event, date and
   configured points.
4. Select another eligible group; confirm standings reset to page one.
5. Search an athlete name of at least three characters; confirm only matching
   rows appear and pagination resets.
6. Follow card links to the competition and event SPA pages.
7. Confirm the former `/cups/{cup}/{event}/{group}/show` route no longer
   renders a Blade page, while cup table and export workflows continue to work.
