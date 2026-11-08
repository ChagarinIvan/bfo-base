# tests

Evaluate tests by behavior protected and regression value. Check acceptance scenarios, failure and
rollback cases, meaningful assertions, boundary coverage and whether tests fail for the real bug.
Look for random ID/date collisions, clock/timezone dependence, ordering, shared state, hidden DB/network
access and brittle implementation mirroring. Application/Domain unit tests must mock repository and
collaborators, not instantiate Eloquent even Factory::make. DB records belong in integration/request
tests. Check PHPUnit attributes, imports, clear names, mock criteria/payload/calls and class @see for
V1 API. Do not demand tests for every line or reversible low-impact formatting changes. A test that
only repeats implementation or asserts a mock setup has little value; explain the missed failure.
