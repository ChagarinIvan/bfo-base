# Quickstart: Cup View SPA

1. Open `/app/cups/:cupId` as a guest: card details, type icon, linked group
   badges, visibility indicator and stages appear; no impressions or management
   controls are visible.
2. Confirm the stage table requests 50 rows initially; change page and ensure
   the standard pagination controls update the query.
3. Filter by a valid stage/competition title and date; each change reloads page
   one and results stay within the selected cup.
   The compact cup-stage response is followed by one bounded events request for
   the displayed stage IDs and their competition names.
4. Open the same page authenticated: cup/stage impressions and all card/row
   controls appear; check their retained URLs and delete confirmations.
5. Confirm `/cups/:cupId/show` is absent, while cup table, export, cache, and
   cup-event form/mutation routes still resolve and redirect to SPA routes.
