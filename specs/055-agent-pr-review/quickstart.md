# Quickstart

1. Run `node --test .github/review/review.test.cjs` without API access.
2. Follow `.github/review/README.md` for trusted installation into master, API secret,
   optional model variable and GitHub permissions.
3. Open same-repository nondraft PR to master; expect six role jobs, max two parallel.
4. Inspect one report with matching commit and six statuses; update PR, expect update.
5. Re-run without credentials: incomplete report/job failure, never clean review.
6. Fork/draft PR must not start model jobs. Changes to instructions apply only after
   trusted merge; candidate instructions do not control current review.
7. Real smoke uses paid API. Record actual result in validation.md; do not invent it.
