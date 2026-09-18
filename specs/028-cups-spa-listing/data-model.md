# Data model: SPA-листинг кубков

## Cup listing criteria

The API command returns the existing read-only domain `Criteria` to the repository:

- `year`: optional `Year` value;
- `name`: optional trimmed search text;
- `visible`: query values `1`/`0`, or absent for authenticated “all”;
- pagination is applied by the standard `Slice`/pagination adapter.

The API boundary applies the visibility policy: anonymous callers always receive
`visible=1`; authenticated callers may omit visibility for all records.

## CupListingItem

Compact read representation:

- `id`, `name`, `year`, `type`;
- `groups` containing only group id and label needed for table links;
- `visible`;
- `created` and `updated` impressions only for authenticated responses.

No cup event collection, calculated points, protocol lines, result blob or export data
belongs to this model.

## Existing entities reused

- `Cup` remains the aggregate and source of active/visible/year/type data.
- `CupGroup` remains derived from the cup type and supplies table link identifiers.
- Existing event repository/query supplies the latest event date.
