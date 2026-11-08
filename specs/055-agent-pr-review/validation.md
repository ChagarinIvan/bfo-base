# Validation, 2026-11-08

## Spec-kit

Clarify: 0 critical ambiguities, checklist 9/9. Plan/research/data-model/contracts/
quickstart completed; plan self-review PASS under user's explicit full-cycle request.
Tasks generated before analyze; analyze found no uncovered requirements or conflicts.
Implement T001–T011 complete, T012 deliberately open. Hooks absent throughout.
Read-only research agent assessed trust boundaries and later reviewed implementation.

## Regression-first and tooling

Initial Node tests RED: implementation module missing. Then 5 tests GREEN.
Additional coverage-limit regression RED: model could incorrectly claim completed;
fixed with trusted prepare metadata and forced partial in publisher. Final 8/8 GREEN.
Covers schema/identity/path failures, duplicates/priorities, missing/partial reports,
escaping, all-six complete, stale/closed PR, bot-owned comment update and older-run guard.
Publisher missing-artifact regression posts incomplete status and marks job failed.
Trusted prepare smoke against real local base/head generated full patch, paths,
role prompt and coverage metadata without model/API calls.

Actionlint 1.7.12 PASS for agent-review.yml and laravel.yml. The check found an
existing numeric MySQL port map lookup in laravel.yml; changed to string key consistent
with its other steps. This is the only unrelated mechanical correction.
New tooling Node tests wired into existing frontend job. No application query changes,
new relations or N+1 introduced.

## Final checks

| Check | Result |
| --- | --- |
| node --test .github/review/review.test.cjs | PASS, 8 tests |
| actionlint both workflows | PASS |
| composer cs | PASS, 0/1095 files need fixing |
| composer stan | PASS, no errors |
| composer rector -- --dry-run | PASS, no changes |
| composer test, bfo_base_test | PASS, 725 tests / 5523 assertions, 15 existing notices |
| npm run ci | PASS, lint/format, typecheck, 85 files / 293 tests, build |
| Application startup | Existing ProductionPhpResponseTest included in full PHP suite |
| git diff --check | PASS |

Socket-based PHP workers required execution outside filesystem/network sandbox;
initial sandbox attempts failed on EPERM, then passed with approved execution.
Frontend retains existing Vite chunk-size warning. Full PHP/frontend each completed once.

## Acceptance and remaining activation

FR-001–012 represented in prompts, schema, trusted workflow, publisher and README.
SC-001–004 verified locally within tooling scope. Six findings producers themselves
have not run against paid API; no claim of quality/coverage from live model is made.
SC-005 / T012 pending: merge trusted controls into master, configure OPENAI_API_KEY,
open/update same-repository nondraft PR and observe six role jobs plus one updated report.
No key was obtained, stored, or sent; no paid API call, commit, push or merge performed.
Installation guide: .github/review/README.md. UI/UX remains source-only by approved scope.

## First live attempt, startup failure

Run 42479209767, job 128727249145 (ddd): credentials and prompt preparation PASS,
Codex Action failed in drop-sudo before model invocation: Could not restrict access
to /run/dbus/system_bus_socket. All six roles failed at that step; publisher correctly
reported incomplete review. Presence of secret confirmed, validity/billing not tested.
Fix prepared locally: supported unprivileged-user with separate UID, read-only source
and isolated writable reports directory. Live success/SC-005 remains pending trusted rollout.
