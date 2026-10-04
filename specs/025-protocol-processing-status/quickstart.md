# Quickstart

1. Create an Event with a protocol. The detail response shows `parsing` immediately.
2. Confirm parsing, identification and rank rebuilding run in separate transactions. Each locks the Event row and checks its token before changing results.
3. Deliver the same stage event twice. Only the first delivery changes data; the second sees a different status and does nothing.
4. Trigger an unknown protocol format. The Event reports `parsingError` and a safe domain message.
5. Trigger an unexpected line save, identification or rank calculation failure. Event catches the error, records the matching status and publishes `EventProcessingFailed` for cleanup after commit.
6. Kill a worker during a stage and let the queue exhaust attempts. Confirm that the database rolls back the interrupted transaction. The Event may remain transitional because the process stopped before its domain catch; record this as an open operational case.
7. Confirm authenticated polling stops on `ready` or an error. Delay one detail response beyond five seconds and confirm no second request starts. Unmount before a delayed `ready` response and confirm no distance request starts.
8. Confirm guest event, distance and protocol line queries expose only `ready` results.
9. Replace a protocol and confirm old results are removed before parsing the new one. A late stage event or failure from the old token must not change the new run.
10. Migrate existing Events with complete, partially identified, empty and missing results. They become `ready`, `identifyingError`, `parsingError` and `parsingError`; each receives a unique token. The database rejects null status and token.

## Runtime budget

The default Horizon worker has a 300 second timeout and 128 MiB memory limit. Redis `retry_after` is 360 seconds. A stage that exceeds the worker limit loses its transaction. If the queue exhausts attempts without entering an Event method's catch, the status stays transitional. Identification currently loads all remaining lines, and rank rebuilding loads affected person IDs into memory. These are the limits to monitor on larger protocols.

The previous benchmark used `storage/tests/2026/17.01.26.html` (657,019 bytes, 130 lines). It measured parsing at 0.537 seconds and 71 MiB, identification at 1.291 seconds and 71 MiB, and rank rebuilding at 1.642 seconds and 77 MiB on the test MySQL instance. Those measurements predate the full-stage transaction model, so they do not establish its maximum lock duration or memory use. Repeat the benchmark with the current implementation and the largest available production protocol before raising the worker limit.
