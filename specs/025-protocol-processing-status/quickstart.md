# Quickstart

1. Create an Event with a protocol. The response and detail page show `parsing` immediately.
2. Confirm the page refreshes status and result lines every five seconds through `identifying` and `rebuildingRanks`.
3. Deliver the same parse-stage event twice. Confirm only the first call in `parsing` creates lines and transitions the Event.
4. Interrupt identification after several lines have been committed. Retry the stage and confirm it skips assigned lines, finishes the remaining lines and starts rank recalculation once.
5. Interrupt rank recalculation and retry it. Confirm it recalculates from stored facts, does not duplicate results and marks Event `ready` only after all affected people complete.
6. Trigger final failures in parsing, identification and rank rebuilding separately. Confirm the authenticated status is respectively `parsingError`, `identifyingError` or `rebuildingRanksError`, each shows its safe `errorMessage` or localized fallback and stops polling. Confirm a replacement protocol clears the old message.
7. Confirm public list/detail, distance and protocol-line requests hide Events unless their status is `ready`.
8. Replace a protocol while processing. Confirm old derived lines are removed and late events cannot move the new processing state backwards, even when its status equals the old stage.
9. Open an authenticated competition event list during processing. Confirm status changes without navigation and polling stops when no visible event is transitional.
10. Migrate existing Events with complete protocol results and confirm they become `ready`; Events without results remain guest-hidden. Confirm no `EventProtocol` table or `failed` status is created by the replacement migrations.

## Transaction-duration check

Measure parsing and protocol-line preparation for the largest available protocol fixture while the Event row is locked. If the transaction duration is unacceptable, parse to `ProtocolLineInput[]` before opening the transaction, then lock Event and verify its file path is still the one that was parsed before saving lines and changing status.

Measure identification query count and peak memory while processing unassigned lines in bounded portions. Repeat the same fixture twice in one worker to verify that cached prompts do not leak across runs. Measure rank stage duration and confirm an interrupted pass leaves Event transitional until retry completes.

Hold an Event lock in a second process and confirm identification, ranks or protocol replacement waits/retries without writing. Parsing instead locks the Event row for its atomic transaction. Expire or lose a held external lock during identification and confirm the worker stops before the next write; after the lock is available, retry the stage and verify saved person links remain. Repeat for the rank stage. Measure each job's upper execution bound and verify configured lock TTL > hard worker timeout and queue `retry_after` > worker timeout; confirm normal completion releases the lock in `finally`. Record that a write between an ownership check and lock expiry is not fenced. Identification and ranks save progress without a stage-wide transaction.

## Локальный замер 2026-09-30

На крупнейшем поддерживаемом fixture `storage/tests/2026/17.01.26.html` (657 019 байт, 130 строк) тестовая MySQL в OrbStack дала: parsing 0,537 с / 547 запросов / пик 71 MiB; identifying 1,291 с / 1 177 запросов / пик 71 MiB; rebuildingRanks 1,642 с / 1 437 запросов / пик 77 MiB. Одна parsing-транзакция длилась 0,534 с; самая длинная транзакция сохранения одного Person — 0,020 с. В первоначальном варианте время удержания внешнего lock по стадиям составляло 0,534 / 1,290 / 1,641 с; после ревизии parsing внешнего lock не берёт. Замер не претендует на верхнюю границу для всех реальных файлов и БД: блокировка здесь инструментирована тестовым lease, а её Redis-реализация проверена отдельно интеграционными тестами.

Два одинаковых протокола обработаны последовательно одним процессом: оба дали 130 строк и `ready`, все шесть lease первоначального варианта были освобождены (`0,534 / 1,290 / 1,641 / 0,578 / 0,736 / 1,916 с`). В текущем варианте parsing lease не создаёт. В коде нет static prompt cache; повтор использует актуальные записи БД. Ожидаемое линейное число запросов связано с сохранением каждой строки и каждого Person; дополнительных загрузок связей на каждую строку в API-тестах не обнаружено. `TTL = 3900 с > Horizon timeout = 3600 с`, `retry_after = 3660 с > timeout`. Истечение TTL непосредственно между `assertOwned()` и записью не защищено fencing token; это принятый остаточный риск текущего дизайна, поэтому обработчики также сверяют token Event перед записью.
