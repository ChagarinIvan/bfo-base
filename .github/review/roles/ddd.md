# ddd

Focus on EventDriven DDD semantics rather than directory naming. Identify aggregate and invariant
boundary; business transitions go through intentional domain methods, not direct adapter assignments.
Check commands vs facts, event naming/payload stability, event recorded for each saved transition,
created/updated Impression, atomic persistence, rollback and exactly one publication per saved
transition. Consumers reading committed data must run after commit. Trace retry, idempotency,
ordering, duplicate delivery and cache invalidation. Check disable vs physical deletion and documented
exceptions for derived data. Do not assume exactly-once delivery or require event sourcing, a broker,
a transactional outbox or CQRS split without a concrete defect/requirement. Reference manifest
exceptions and feature spec; do not confuse a projection refresh with a new business event.
