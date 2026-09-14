# Data Model

## EventProtocol

Отдельный aggregate для каждого run загрузки: `id`, `eventId`, `runToken`, `status`, `totalLines`, `identifiedLines`, `rankBatchId`, `rankJobsTotal`, `rankJobsCompleted`, `completedRankPersonIds`. `queued → parsing → identifying → rebuilding-ranks → ready`, with `failed` reachable from a processing stage. `ready` and `failed` are terminal for polling; `parsing` допускается только из `queued`.

`events.active_event_protocol_id` указывает на единственный актуальный run. Каждая строка протокола хранит `event_protocol_id`; связь индексируется вместе со статусом run для чтения списка и проверки готовности. Затронутые спортсмены передаются в созданный для run rank batch, а не агрегируются как изменяемый список внутри `EventProtocol`.

## Invariants

- `Event` указывает на актуальный `EventProtocol`; public event requires its active protocol to be `ready`.
- Ready requires a stored protocol, no line of this run with `person_id = null`, and completed event-scoped rank rebuild work.
- Protocol lines reference their `EventProtocol`; application services accept their signals only while this run is the event's active run, so stale runs are ignored.
- Идентификация одной строки учитывается не более одного раза: обработчик проверяет принадлежность строки run и атомарно фиксирует её в счётчике/журнале run.
- Для run создаётся ровно один rank batch. Его completion принимается только при совпадении сохранённого `rankBatchId` и один раз для каждого `personId`; повторная доставка completion не меняет terminal state.
- После вызова mutation-метода aggregate repository может быть вызван безусловно: Eloquent не выполняет SQL update для clean model. Результат mutation-метода применяется только для следующего side effect.
- Исторический этап с сохранёнными результатами получает готовый active run при миграции; этап без протокола active run не получает.
- Only authenticated readers receive non-ready state details.
