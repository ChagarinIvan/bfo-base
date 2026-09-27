# Data Model: Count-Free Slice Pagination

## Slice result

The domain result for one listing page.

| Field | Type | Rules |
|---|---|---|
| `items` | ordered list of domain values/DTO source objects | Contains at most `perPage`; never contains the probe row |
| `currentPage` | positive integer | 1-based, preserves the existing request model |
| `perPage` | positive integer | Validated and capped by the existing safe maximum |
| `hasNext` | boolean | True iff the bounded read returned a row beyond `perPage` |

The Slice must not contain or derive `total` or `lastPage`. Calling item iteration, JSON serialization, mapping, or header generation must not issue a count query.

## Slice read adapter

An infrastructure adapter supplies an ordered iterable for the requested offset and bounded length. The shared Slice owns the algorithm:

1. Calculate offset from `currentPage` and `perPage`.
2. Read `perPage + 1` rows once.
3. Set `hasNext` when the extra row exists.
4. Retain only the first `perPage` rows.

The adapter must not expose a `getNbResults()` operation to the Slice path.

## Listing criteria

Existing domain Criteria remain the input for filters and sorting. The feature does not add hidden pagination flags or relation-loading flags to Criteria. Each repository documents:

- accepted filter parameters and normalization;
- default and caller-provided ordering, including the unique tie-breaker;
- joins/subqueries/distinct needed for relationship filters;
- root fields selected and related resources required by the application service;
- empty/invalid filter behavior.

## Listing resource set

An application-owned declaration of relations/fields required to serialize a listing result. Infrastructure loads only declared resources and must not add per-row lazy-loading. Existing typed resources, such as event resources, remain the source of truth where available.

## Listing query profile

An implementation/review record, not a persisted business entity.

| Field | Meaning |
|---|---|
| Listing | Repository/service/action and endpoint |
| Criteria matrix | Each supported filter and relevant combinations |
| Root query | Root table, selected columns, ordering |
| Relations | Joins, eager loads, aggregates and duplicate prevention |
| Index evidence | Existing/proposed indexes and the predicates they support |
| Baseline/after | Representative latency, SQL count, and explain plan |
| Regression result | Data equivalence, ordering and N+1 outcome |
