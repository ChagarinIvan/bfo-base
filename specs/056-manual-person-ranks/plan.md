# Implementation plan: Manual person ranks

**Branch**: `056-manual-person-ranks` | **Date**: 2026-11-08 | **Spec**: [spec.md](spec.md)

## Summary

Постоянный ManualPersonRank aggregate отдельно от person_rank_histories projection.
Eloquent-агрегат наследуется от `AggregatedModel`. Репозиторий использует Eloquent,
события с неизменяемым payload доставляются после commit. Add/disable services lock Person, сохраняют source и синхронно
вызывают существующий rebuild в одной transaction. Source metadata не заменяет history ID.

## Technical context

PHP 8.5/Laravel 13/MySQL, Vue/PrimeVue/TypeScript. Новых dependencies нет.
Manual UUID генерируется существующим UuidGenerator. Repository byCriteria/oneByCriteria,
Domain factory выполняет duplicate check под Person lock. One source query per person,
без N+1 для чтения истории. Collector объединяет protocol и manual facts.
Migration делает protocol references nullable и добавляет manual_rank_id.

## Constitution check

Согласованное исключение из раздела I конституции: `ManualPersonRank` следует принятой
реализации существующих Eloquent-агрегатов. Репозиторий находится в Infrastructure.
Aggregate create/disable фиксируют events, Impression created/updated, save через port
в transaction. События реализуют `ShouldDispatchAfterCommit`. При rollback доставка отменяется.
Person пересчитывается синхронно, source/projection atomic. Никакой очереди между ними.
Soft disable сохраняет source. Physical delete разрешён только старой derived projection.
V1 thin actions, command getters не возвращают DTO; camelCase, serializer, @see tests.
Unit-тесты используют моки. Сохранение, аудит, события и пересчёт проверяются в integration-тестах.

## Structure

app/Domain/Person/ManualRank/{ManualPersonRank,ManualRankInput,ManualPersonRankRepository,ManualPersonRankFactory,Exception/*,Event/*}.php
app/Infrastructure/Laravel/Eloquent/Person/ManualRank/EloquentManualPersonRankRepository.php
app/Application/{Dto/Person/ManualRank/*,Service/Person/ManualRank/*}.php
app/Bridge/Laravel/Http/Controllers/Api/V1/Person/ManualRank/*Action.php
Existing RankFact, RankCalculator, PersonRankHistory, collector, assembler/DTO, providers/routes.
Migration 2026_11_08_000001_add_manual_person_ranks.php.
resources/spa/api/personRankHistory{,.test}.ts, types.ts,
pages/persons/PersonRanksPage{,.test}.vue/ts, components/ManualPersonRankDialog{,.test}.vue/ts,
lang/by.json, updates entry and rank reason captions/tests.

## Design decisions

Both dates required, achieved<=today and activated>=achieved. Future activation is scheduled
by existing daily selection for every rank. Manual junior bypasses age filter; manual facts
never count as protocol starts. Only an explicit protocol IIIю suppresses the IIIю awarded
for three starts in that year. Two manual facts can extend each other despite null eventId.
Repeat qualifications retain the latest finish date of the shared active period.
Sort same-day protocol facts as before, then manual by UUID, keeping repeat rebuild stable.
Prune query excludes active manual sources to preserve manual-only participants.
No automatic dedup across sources. Same active manual tuple rejected; disabled tuple reusable.

## Review

Spec reviewed with 2 answered clarification questions. Plan reviewed against all 12 FR,
all assumptions resolved. User requested full spec-kit; proceed to tasks/analyze/implement.
Hooks absent. No new architectural exception beyond documented legacy projection boundary.
