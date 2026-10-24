# Модель данных

## RankAuditEntry

Чистая immutable запись с шестью полями: personId, trigger: RankAuditTrigger, created: Impression, before: ?RankSnapshot, after: ?RankSnapshot, id: ?int. У сохранённой записи есть id. after=null означает неуспешную попытку; before может быть неизвестен при ранней ошибке. У успешного изменения before обязателен. Нет словаря values, строкового trigger, статуса, дублирующих флагов, входных фактов или идентификаторов цепочки.

## RankSnapshot и RankHistorySnapshot

Снимок хранит PersonRank, список RankHistorySnapshot и ближайшую активацию. Элемент истории имеет типизированные IDs источника, Rank, RankChangeType и даты выполнения/активации/периода. Сравнение использует содержимое и канонический порядок, не ID строк сохранённой проекции. JSON-массивы создаются только mapper/assembler на внешних границах.

## Хранение

person_rank_audit: id, person_id, trigger, created_at, created_by, before_state JSON nullable, after_state JSON nullable. Индекс person_id/id. Нет FK на пересоздаваемую историю/удаляемый протокол. Успех сохраняется атомарно с состоянием; ошибка после rollback. Без изменений новая запись не создаётся.

## ProtocolLine

created_at/created_by и updated_at/updated_by nullable для неизвестных старых данных. Фабрика получает Impression текущего импорта и устанавливает оба аудита. Мутации сохраняют created и обновляют updated. Общий ImpressionCast возвращает null для неизвестных старых значений. Флага применимости повторной активации нет.
