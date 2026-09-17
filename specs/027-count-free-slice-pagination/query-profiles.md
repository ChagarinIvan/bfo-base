# Listing Query Profiles

## Baseline and final measurements

The implementation must record representative first-page and filtered-page latency, SQL query count, and explain plans for each heavy listing family before and after changes. Runtime values are intentionally left blank until the test database and representative fixtures are available.

| Listing family | Baseline | After | Decision |
|---|---|---|---|
| Persons | pending runtime capture | pending | pending |
| Events | pending runtime capture | pending | pending |
| Groups | pending runtime capture | pending | pending |
| Competitions | pending runtime capture | pending | pending |
| Clubs | pending runtime capture | pending | pending |
| Payments/prompts | pending runtime capture | pending | pending |
| Protocol lines | pending runtime capture | pending | pending |
| Rank checks/rows | pending runtime capture | pending | pending |

## Evidence rule

Add a composite index or change a join/resource path only when the before/after plan and result tests show a measurable benefit without duplicate roots, a new N+1, or a regression in another listing family.
