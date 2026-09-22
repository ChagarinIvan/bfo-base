# Quickstart: Restore repeat master-rank activation

## Prerequisites

- Local MySQL is available through the project environment.
- Test database migrations and factories are usable.

## Validate the fast identification path

1. Create an athlete with an older activated MS (repeat separately for KMS).
2. Create a newly imported MS line for that athlete's prepared identity, with no activation.
3. Run fast protocol identification.
4. Verify the new line has the event date in `activate_rank` before its rebuild job is
   dispatched.

## Validate the queue/console path

1. Create equivalent history and an unassigned queued identity line.
2. Run `protocol-lines:queue-ident` with a user ID.
3. Verify all equal newly assigned lines have the event date in `activate_rank` before
   the rank rebuild is invoked.

## Regression checks

- First and unactivated-prior KMS/MS achievements remain unactivated.
- A prefilled activation date is preserved.
- A batch with several athletes performs one set-based prior-activation lookup, without
  a per-athlete `exists()` query.
- Run the targeted feature and command tests, then the project PHP quality gates at
  feature completion.
