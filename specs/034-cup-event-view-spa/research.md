# Research: Cup Event View SPA

## Decisions

### Use one cup-event identity in the SPA route

**Decision**: Route the page by `cupEventId`, not by cup ID plus a group ID.

**Rationale**: A cup-event is already an independent aggregate and its
repository joins active cups. The card can derive cup context; group is a
table filter rather than part of the page identity.

**Alternatives considered**:

- Retain `/cups/:cupId/:event/:group/show`: rejected because it preserves the
  legacy route shape and creates multiple pages per one cup event.
- Use event ID: rejected because the same event can be associated with a
  different cup stage and loses configured points.

### Represent tabs as a shared select control

**Decision**: Use the established SPA select control for eligible cup groups.

**Rationale**: It remains usable with many groups, maps to the user-requested
selector, and has a single URL/page identity.

### Keep scoring calculation as its current authority

**Decision**: Reuse cup-type `calculateEvent` semantics and move delivery
concerns around it into target Application/DTO/API layers.

**Rationale**: Point calculation needs the group result set to identify the
winner and relative scores; duplicating scoring in an SQL listing risks subtle
ranking regressions. The page still sends only a requested `Slice` of DTOs.

**Alternatives considered**:

- Paginate raw protocol lines before calculation: rejected because the first
  scorer and resulting points may be outside the page.
- Reimplement every cup type in database queries: rejected as a scoring-domain
  rewrite outside this migration.

### Filter after calculated standings

**Decision**: Normalize athlete name and filter calculated point rows before
forming the response slice.

**Rationale**: The score remains calculated against the complete selected
group. Filtering source protocol lines first could change scores.

### Remove only uniquely unused legacy support

**Decision**: Delete the Blade action, template, route and tests, then remove
`app/Services` methods/classes only when a repository-wide usage search proves
they have no remaining caller.

**Rationale**: Cup tables and exports still rely on legacy code and must not
be broken by this migration.
