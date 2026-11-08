# Research

## Постоянный источник

PersonRankHistory пересоздаётся EloquentPersonRepository::update() через delete/insert.
Постоянные ручные факты нельзя хранить только в этой таблице. RankFactsCollector
используется RebuildPersonRanksService и StandardEventPersonRankUpdater, поэтому общий
collector — точка включения источника во все существующие production paths.

## Идентичность и nullable references

RankFact и PersonRankHistory сейчас требуют protocol/distance/event/competition IDs.
Нужны nullable protocol references, source type и стабильный manualRankId.
Projection ID не подходит для удаления: после rebuild он меняется.
Calculator копирует history в extendActivePeriods/endLowerRankPeriods: source identity
должна сохраняться в обеих ветках. Сравнение eventId !== eventId не работает для двух
manual facts с null event: им нужен distinct-source-aware extension predicate.
Сортировка должна учитывать source identity при одинаковых датах/nullable protocol IDs.

## Автоматические правила

Manual не входит в три старта для synthetic IIIю. Future explicit activation должна
попасть в nextActivationOn даже без delayedActivation, включая взрослые разряды.
Age filter для protocol остаётся; manual junior bypass подтверждён пользователем.
Без bypass source оставался бы в БД, но исчезал из UI и лишался delete affordance.

## Архитектура

Для нового source чистая Domain entity, repository port, Infrastructure storage model
и mapper. Не наследовать новый aggregate от AggregatedModel/AggregatedEvent:
они привязаны к Laravel. Source event plain readonly; Infrastructure dispatch afterCommit.
Mutation lock Person в transaction сериализует duplicate checks и rebuild.
Без расширения legacy Services/Repositories; thin API command use cases.

## SPA

PersonRanksPage уже имеет rebuild и activation dialog; PrimeVue + shared ConfirmDeleteDialog.
Нужны add dialog с rank и двумя датами, source label и manual-only delete.
Nullable IDs фильтруются до events/cups calls, manual-only история не запрашивает события.
После add/delete обновляются history и parent Person через refresh query, как rebuild.
Route changes не должны применить результат старой mutation к другому Person.

Read-only research выполнен двумя speckit-plan research agents, без edits.
Decisions consolidated in plan: UUID source, SQL mapper, synchronous rebuild, afterCommit events.
Alternatives rejected: source only in history (lost on rebuild), fake protocol rows/IDs (wrong provenance),
queued rebuild (partial visible state), new Eloquent Domain aggregate (violates constitution).

## Review findings incorporated

Same-day order explicitly protocol first, then manual UUID; red regression reproduced null
comparison ordering and verified correction. PruneInactivePersonsCommand selected manual-only
people through withoutLinesAndPayments: correlated NOT EXISTS active manual source added
to that query, with regression first. This protects the new source, not a new cleanup policy.
Frontend read-only reviewer found post-delete unmount race; page live guard and regression added.
