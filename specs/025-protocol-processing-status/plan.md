# Implementation Plan: Статусы обработки протокола этапа

**Branch**: `025-protocol-processing-status` | **Date**: 2026-09-13 | **Spec**: [spec.md](spec.md)

## Summary

Сохранять состояние конкретной загрузки в отдельном `EventProtocol` и менять его в фоновых потоках разбора, идентификации и пересчёта разрядов. `Event` хранит ссылку только на актуальный run. Авторизованный API возвращает состояние в карточке и списке; публичное чтение доступно только готовому актуальному run. SPA показывает локализованный статус и обновляет карточку каждые пять секунд лишь в переходных состояниях.

## Technical Context

PHP 8.5 / Laravel 13, TypeScript / Vue 3 / PrimeVue, MySQL/Eloquent и Redis/Horizon. Состояние, счётчики, уникальные `personId` завершённых rank-job и идентификатор rank batch хранятся в `event_protocols`; `events.active_event_protocol_id` указывает на актуальный run. Покрытие: PHPUnit unit/API/integration и Vitest. Polling ограничен одной открытой карточкой, обновляет и этап, и его строки, прекращается при `ready`, `failed` и unmount; новые read paths не добавляют N+1.

## Constitution Check

| Gate | Status | Rationale |
|---|---|---|
| I. Layering | Pass | Domain state/value object и transition policy; Application commands/use cases; Bridge/Infrastructure adapters. |
| II. No facades | Pass | Новый код получает зависимости через конструктор. |
| III. Tests | Pass | Переходы, ready-invariant, API visibility, UI и polling имеют отдельные тесты. |
| Performance | Pass | Один bounded refresh раз в 5 секунд только в transition state. |

## Design

### EventProtocol aggregate

`EventProtocol` is one immutable-run aggregate per upload: `id`, `eventId`, `runToken`, counters for created/identified lines, rank-batch state, completed rank-job person IDs and `queued`, `parsing`, `identifying`, `rebuildingRanks`, `ready`, `failed` status. Parsing starts only from `queued`; terminal and repeated delivery cannot regress state. `Event` points to the active run; application services ignore parser, identification and rank-start signals for a replaced run.

### Target boundaries

- `app/Domain/Event/`: `EventProtocol`, its invariants/transitions/domain events, and Event's active-protocol reference.
- `app/Application/Service/Event/`: commands/use cases, coordinating transitions and persistence.
- `app/Infrastructure/Laravel/Eloquent/Event/`: migration, cast и query projection.
- `app/Bridge/Laravel/`: queued handlers, console command и HTTP actions создают commands. Existing `ParserService` and `ProtocolLineIdentService` are legacy adapters only; they receive no new status logic.

### Completion orchestration

`ProtocolLine::setPerson()` publishes its aggregate event with `eventProtocolId`. Application handler records identification only while that run remains active; duplicate delivery does not increment its counter. Once every line is identified, `EventProtocol` creates exactly one event-scoped rank batch and stores its identity. A completion carries the batch identity and `personId`; it is counted once per person, so retry delivery cannot make the run ready early. Completion is scoped to the referenced run and therefore cannot change a newer run. Aggregate calls are followed by repository `update()`; Eloquent persists only dirty aggregates, while the returned transition result gates subsequent side effects such as queue dispatch.

### Historical data migration

The migration creates an `EventProtocol` for every historical event that has persisted protocol results, copies the event audit impression, links its historical protocol lines to that run and makes it active with `ready` status; the status is not inferred merely from elapsed time. Historical events without a protocol stay without an active run and therefore remain unavailable publicly. The migration is idempotent and is covered by integration tests; it must not invent a ready run for partial or ambiguous historical data.

### API and SPA

Authenticated DTO/list projection содержит `processingStatus`. Read commands receive nullable `UserId`, so guest access is represented outside HTTP request objects. Public list/detail requires `ready`; этап без протокола, в обработке или с ошибкой не раскрывается гостю. Shared status component показывает loader, warning, terminal error and ready state. Detail polling keeps the last successful result on a temporary refresh error and refreshes protocol lines together with the event.

## Project Structure

```text
app/{Domain/Event,Application/Service/Event,Infrastructure/Laravel/Eloquent/Event,Bridge/Laravel}/
resources/spa/{components,pages/events}/
tests/{Domain/Event,Application/Service/Event,Feature/Api/V1/Event}/
```

## Complexity Tracking

No constitution violations or justified exceptions.
