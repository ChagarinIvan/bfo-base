---
name: architecture-audit-bfo
description: Audit BFO Base architecture against its constitution and propose focused refactoring candidates without changing code. Use for periodic architecture reviews, layer-boundary questions, or requests to improve maintainability and testability.
---

# BFO Architecture Audit

Perform a read-only architecture audit of BFO Base. The goal is to find a small number of
high-leverage refactoring candidates that improve locality, testability, and clarity while
preserving the project's migration direction.

## Sources of truth

Read these before making findings:

- `AGENTS.md` and `CLAUDE.md` for repository workflow and coding rules.
- `.specify/memory/constitution.md` as the authoritative architecture and quality policy.
- The relevant feature artifacts under `specs/`: `spec.md`, `plan.md`, `data-model.md`,
  `contracts/`, and `tasks.md` when the audit concerns a feature.
- Existing ADRs or domain documentation when present.

The constitution wins over generic refactoring preferences. Treat existing legacy code as context,
not as a reason to propose a broad rewrite.

## Scope and safety

- Do not edit source code, tests, specs, migrations, configuration, or documentation during the
  audit.
- Do not create new interfaces, services, repositories, or abstractions as part of the audit.
- Inspect the current working tree and preserve uncommitted changes in all reporting.
- Prefer a focused module or seam over a repository-wide cleanup.
- Use `rg`/`rg --files` for navigation when the project index is unavailable; use the project
  index when available for definitions and usages.

## What to inspect

Prioritize recently changed or repeatedly touched areas, then inspect the relevant call graph:

1. Layer direction: Domain must not depend on Laravel; Bridge adapts HTTP/framework concerns;
   Application orchestrates use cases; Infrastructure owns Eloquent and external details.
2. Legacy containment: new code must not expand `app/Services`; Application services/use cases in
   `app/Application/Service` are allowed target-layer orchestration.
3. Interface depth: identify pass-through modules, duplicated orchestration, leaky DTO/model
   conversions, and seams that are difficult to test through public behavior.
4. Data access: look for N+1 queries, unbounded list reads, accidental lazy loading, and queries
   that mix legacy and V1 projections.
5. Contract locality: compare actions, DTOs, use cases, repositories, assemblers, frontend API
   clients, and tests against the feature contract. Flag behavior that is duplicated or enforced
   at inconsistent layers.
6. Test seams: identify important behavior tested only through internals, or important public
   paths with no regression coverage.

## Findings

Report no more than five prioritized candidates. For each candidate include:

- **Location**: exact files/classes and the relevant call path.
- **Observation**: concrete evidence from the code or tests.
- **Constitution impact**: the specific principle or rule involved, if any.
- **Risk**: what can break or become harder to change.
- **Refactoring direction**: a bounded next step, without implementing it.
- **Verification**: the test, static check, query check, or acceptance scenario that would prove
  the change safe.

Separate hard constitution violations from improvement opportunities. Do not call a class a
violation merely because its name contains `Service`: `app/Application/Service` is a permitted
target layer; `app/Services` is the prohibited legacy namespace.

End with:

- the strongest candidate and why it has the best leverage;
- items explicitly not worth changing now (if applicable);
- a suggested follow-up spec/task, without creating it.
