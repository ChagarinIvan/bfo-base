# BFO Base — GitHub Copilot review instructions

When reviewing pull requests, review only the changed code and directly affected behavior.
Do not report unrelated pre-existing issues unless the change makes them worse.

## Architecture

The target architecture has four layers:

- `Domain` — framework-independent business rules and entities.
- `Application` — use cases, orchestration, DTOs and assemblers.
- `Bridge` — HTTP/controllers/framework adapters.
- `Infrastructure` — Eloquent, persistence and external integrations.

`app/Services` is legacy code. Do not recommend adding new classes there.

Application services and use cases under `app/Application/Service` are allowed and preferred
for application orchestration. Do not flag an Application service merely because its class name
ends with `Service`.

Domain code must not depend on Laravel, Eloquent, HTTP, facades or framework helpers.
Laravel-specific behavior belongs in Bridge or Infrastructure.

Prefer constructor dependency injection and interfaces where they provide a real seam.
Do not suggest speculative abstractions or repositories without a concrete need.

## Review priorities

Pay particular attention to:

1. Violations of the Application / Domain / Bridge / Infrastructure dependency direction.
2. Business logic leaking into controllers, Eloquent models or frontend components.
3. New code accidentally extending legacy `app/Services`.
4. N+1 queries, lazy loading in list endpoints, unbounded queries and incorrect pagination.
5. Missing active-record filtering and incorrect soft-delete behavior.
6. DTO/API contract mismatches, especially field naming and response envelopes.
7. Missing validation at the HTTP boundary.
8. Authentication and authorization regressions.
9. Missing regression tests for changed behavior.
10. Changes that contradict the relevant files under `specs/`.

## PHP conventions

The project uses PHP 8.5 and Laravel 13.

- Use imported class names with `use`; do not write inline fully-qualified class names.
- Do not use Laravel facades in application code.
- Keep API actions thin.
- Keep domain rules in the Domain layer.
- Keep Eloquent details in Infrastructure.
- Preserve existing legacy Blade routes and legacy API behavior unless the PR explicitly migrates them.
- Do not introduce a new legacy service or repository in `app/Services`.

## SPA conventions

The frontend uses Vue 3, TypeScript, PrimeVue, Pinia, Vue Router and Axios.

- Keep `/app/*` SPA routes separate from legacy routes.
- Use the central API client.
- Keep API calls typed.
- Reuse shared components instead of duplicating interaction logic.
- Preserve API `camelCase` fields and existing response contracts.
- Check loading, empty, validation-error and unauthorized states.
- Check direct page refresh/deep-link behavior for new SPA routes.

## Specification review

Before judging feature behavior, inspect the relevant:

- `specs/<feature>/spec.md`
- `specs/<feature>/plan.md`
- `specs/<feature>/data-model.md`
- `specs/<feature>/contracts/`
- `specs/<feature>/tasks.md`
- `.specify/memory/constitution.md`

Verify that the implementation matches the specification, plan and contracts.
If the specification and implementation disagree, report the concrete mismatch and cite both files.

Treat the constitution as authoritative. Do not propose weakening or bypassing it to make the
implementation easier.

## Tests and quality gates

Changed behavior should have appropriate tests:

- PHPUnit unit tests for application/domain behavior.
- PHPUnit request tests for API behavior.
- Vitest tests for SPA models, stores and components.
- Query-count or query-shape checks for list endpoints when N+1 is relevant.

When appropriate, verify:

- `composer test`
- `composer stan`
- `composer cs`
- `composer rector -- --dry-run`
- `npm run ci`

Do not require the entire test suite for documentation-only changes. Distinguish test failures
caused by the environment or database state from actual implementation failures.

## Review comment rules

Report only actionable findings.

For every finding include:

- severity: `blocking`, `important` or `nit`;
- exact file and line/hunk;
- concrete failure scenario;
- why it violates the specification, constitution or project convention;
- a focused fix direction.

Do not report stylistic preferences as blocking issues.
Do not suggest replacing a valid Application service with a handler merely because it is named
`Service`.
Do not suggest broad rewrites when a local fix is sufficient.
Prefer a small, testable correction with a clear regression test.
