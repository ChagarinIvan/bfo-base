# Implementation Plan: Статусы обработки протокола этапа

**Branch**: `025-protocol-processing-status` | **Date**: 2026-09-13 | **Spec**: [spec.md](spec.md)

## Summary

Сохранять состояние конкретной загрузки в отдельном `EventProtocol` и менять его в фоновых потоках разбора, идентификации и пересчёта разрядов. `Event` хранит ссылку только на актуальный run. Авторизованный API возвращает состояние в карточке и списке; публичное чтение доступно только готовому актуальному run. SPA показывает локализованный статус и обновляет карточку каждые пять секунд лишь в переходных состояниях.

## Technical Context

PHP 8.5 / Laravel 13, TypeScript / Vue 3 / PrimeVue, MySQL/Eloquent и Redis/Horizon. Состояние, счётчики и идентификатор rank batch хранятся в `event_protocols`; `events.active_event_protocol_id` указывает на актуальный run. Покрытие: PHPUnit unit/API/integration и Vitest. Polling ограничен одной открытой карточкой и прекращается при `ready`, `failed` и unmount; новые read paths не добавляют N+1.

## Constitution Check

| Gate | Status | Rationale |
|---|---|---|
| I. Layering | Pass | Domain state/value object и transition policy; Application commands/use cases; Bridge/Infrastructure adapters. |
| II. No facades | Pass | Новый код получает зависимости через конструктор. |
| III. Tests | Pass | Переходы, ready-invariant, API visibility, UI и polling имеют отдельные тесты. |
| Performance | Pass | Один bounded refresh раз в 5 секунд только в transition state. |

## Design

### EventProtocol aggregate

`EventProtocol` is one immutable-run aggregate per upload: `id`, `eventId`, `runToken`, counters for created/identified lines, rank-batch state and `queued`, `parsing`, `identifying`, `rebuildingRanks`, `ready`, `failed` status. It changes state only through intentional methods and records domain events. `Event` points to the active run; stale run signals are ignored.

### Target boundaries

- `app/Domain/Event/`: `EventProtocol`, its invariants/transitions/domain events, and Event's active-protocol reference.
- `app/Application/Service/Event/`: commands/use cases, coordinating transitions and persistence.
- `app/Infrastructure/Laravel/Eloquent/Event/`: migration, cast и query projection.
- `app/Bridge/Laravel/`: queued handlers, console command и HTTP actions создают commands. Existing `ParserService` and `ProtocolLineIdentService` are legacy adapters only; they receive no new status logic.

### Completion orchestration

`ProtocolLine::setPerson()` publishes its aggregate event with `eventProtocolId`. Application handler atomically and idempotently records the line identification in that run; duplicate delivery does not increment its counter. Once every line is identified, `EventProtocol` creates exactly one event-scoped rank batch and stores its identity. A batch-completion signal with the matching identity atomically invokes `EventProtocol::completeRanks()` and then `EventProtocol::markReady()`; repeated or stale signals do nothing. `runToken` and the active-run reference reject signals from a replaced upload.

### Historical data migration

The migration creates an `EventProtocol` for every historical event that has persisted protocol results and makes it the active run with `ready` status; the status is not inferred merely from elapsed time. Historical events without a protocol stay without an active run and therefore remain unavailable publicly. The migration is idempotent and is covered by integration tests; it must not invent a ready run for partial or ambiguous historical data.

### API and SPA

Authenticated DTO/list projection содержит `processingStatus`. Public list/detail requires `ready`; этап без протокола, в обработке или с ошибкой не раскрывается гостю. Shared status component показывает loader, warning, terminal error and ready state. Detail polling keeps the last successful result on a temporary refresh error.

## Project Structure

```text
app/{Domain/Event,Application/Service/Event,Infrastructure/Laravel/Eloquent/Event,Bridge/Laravel}/
resources/spa/{components,pages/events}/
tests/{Domain/Event,Application/Service/Event,Feature/Api/V1/Event}/
```

## Complexity Tracking

No constitution violations or justified exceptions.
