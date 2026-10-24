# Модель данных простого аудита

## RankAuditAction

Чистое readonly значение: personId:int, trigger:RankAuditTrigger, created:Impression, current:PersonRank, nextActivationOn:?Carbon, id:?int. current обязателен; отсутствие разряда выражено Rank::WithoutRank. Nullable спортивные даты сохраняют смысл существующего PersonRank.

Person::updateRanks(state, impression, trigger) добавляет action в pending список rankAudit. Repository сохраняет его вместе с rankHistoryToPersist и проекцией; после успеха очищает pending аудит и payload истории, чтобы обычное повторное сохранение не удаляло уже записанную историю. Повторный updateRanks создаёт новое действие; обычное повторное сохранение Person без updateRanks не дублирует запись. Накопленный журнал читается отдельно постранично, не загружается как часть pending списка.

## Хранение

person_rank_audit: id, person_id, trigger, created_at, created_by, applied JSON NOT NULL. Индекс person_id/id. JSON содержит rankId, startedOn, activatedOn, finishedOn, nextActivationOn. RankAuditModel Infrastructure преобразует поля в/из PersonRank; отдельного snapshot mapper нет. Удаление/пересоздание истории и протоколов не затрагивает аудит. Удаление аудита не является частью refill.

RankAuditRepository предоставляет только byCriteria. Активность Person проверяется в query; Domain не зависит от Infrastructure model.

## RankAuditTrigger

Manual, Scheduled, ProtocolImport, ProtocolCleanup, Activation, Reassignment. Причина приходит из инициирующей точки; job сохраняет её. Нет Queued, Refill, Correction. Механизм запуска full refill использует Manual.

## DTO

RankAuditDto: id:string, personId:int, trigger:RankAuditTrigger, created:ImpressionDto, applied:AppliedRankDto. AppliedRankDto: rankId:int, startedOn/activatedOn/finishedOn/nextActivationOn:?string. Даты результата YYYY-MM-DD, created.at стандартного формата Impression. Не используются ?array или array<string,mixed> в API-контракте.

## ProtocolLine

created/updated:Impression обязательны. Миграция переносит created_at/by и updated_at/by из события через дистанцию, затем задаёт NOT NULL. Фабрика новых строк ставит оба значения текущего импорта; мутации сохраняют created. Исторического отдельного nullable cast нет.
