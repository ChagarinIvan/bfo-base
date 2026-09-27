# Research: Count-Free Slice Pagination

## Decision: Replace total-based Pagerfanta semantics in the Slice path

**Finding**: `App\Domain\Shared\Pagination\Slice` currently wraps Pagerfanta. Its `paginationHeaders()` calls `getNbResults()` and `getNbPages()`, while `EloquentQueryAdapter::getNbResults()` clones the Eloquent query and calls `count()`.

**Decision**: Make the shared Slice represent one bounded page plus `hasNext`, without requiring a total or last page. The adapter/query seam must fetch the page using `offset((page - 1) * perPage)` and `limit(perPage + 1)`.

**Rationale**: It directly removes the expensive operation and matches the user’s required algorithm. Keeping Pagerfanta as the owner of Slice state would retain a mandatory total-count method and make accidental count calls easy.

**Alternatives considered**:

- Keep Pagerfanta and provide a fake count: rejected because metadata would be false and Pagerfanta can still trigger count-dependent behavior.
- Use Laravel `simplePaginate`: rejected because the project already has a Domain Slice and custom adapter seam; adopting a framework paginator would leak transport/framework semantics into Domain.
- Introduce cursor-token pagination: rejected by the clarified scope; it changes the existing page/perPage UX and API.

## Decision: Use `X-Pagination-Has-Next` as the only new navigation signal

**Finding**: `ApiAction` serializes a Slice as an array and appends `paginationHeaders()`. SPA `paginationFromHeaders()` and multiple paginator components currently require `total` and `lastPage`.

**Decision**: Preserve the JSON array and `X-Pagination-Current-Page`/`X-Pagination-Per-Page`; add `X-Pagination-Has-Next: true|false`; remove `X-Pagination-Total` and `X-Pagination-Last-Page`.

**Rationale**: It is a small, explicit transport change and avoids changing every DTO response shape. SPA controls can use current page, page size, `hasNext`, and whether the current page is non-empty.

**Alternatives considered**:

- Put metadata in a `{data, meta}` JSON envelope: rejected because it is a wider API/serializer change than required.
- Return both old and new headers: rejected because old headers require a count or misleading values.

## Decision: Keep one foundational no-COUNT regression test

**Decision**: Add one shared Slice/adapter test that observes the query collaborator and proves: with `perPage = 20`, exactly 21 rows are requested; the 21st row is omitted; `hasNext` is true; no count method/query is called. Add separate listing/API/SPA tests only for behavior and contract migration.

**Rationale**: The algorithm is centralized, so repeating the same SQL-count assertion in every listing adds noise. Listing-specific tests still protect filter/join/order semantics.

## Decision: Audit query plans before adding indexes or changing joins

**Finding**: Paginated repositories include direct filters and relationship filters through joins, `distinct`, `withCount`, and optional relations. Examples include persons by club/rank/name, events by competition/group/date/cup relation, groups by event/name, and payments/prompts/rank rows by parent identifiers.

**Decision**: Build a listing matrix and query profile for every paginated repository. Measure baseline and after plans with representative data. Add a migration only when an index is supported by an actual filter/join/order path and does not regress the wider matrix. Use joins for filtering and typed resources/eager loading only for fields required by the result.

**Rationale**: Composite index order and join strategy depend on actual predicates and cardinality; speculative indexes can increase write cost and still fail to support the ordered query.

**Alternatives considered**:

- Add indexes to every filter column: rejected as unmeasured schema growth.
- Replace every join with eager loading: rejected because filtering belongs in SQL and eager loading can create large intermediate sets.
- Make every relation eager by default: rejected because it risks N+1/over-fetching and violates explicit resource loading.

## Decision: Stable page-based ordering is mandatory

**Decision**: Every Slice query must end with a deterministic unique tie-breaker, normally the primary key, after its business sort fields. Relationship filters must preserve one root row per entity through the chosen join/distinct strategy.

**Rationale**: Offset pages are only meaningful when equal sort values have a stable order; otherwise records can repeat or disappear between pages even without a count query.
