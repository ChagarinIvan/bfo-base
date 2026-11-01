# Data model

Новые таблицы, поля и миграции не нужны.

- Group: id, name, normalize_name, active. Поиск использует активные записи и
  общий normalizer. В рамках импорта normalized name соответствует одной группе.
  При исторических дублях сохраняется первое совпадение из repository result.
- Distance: event_id, group_id, length, points. Кэш distance живёт один create(),
  поэтому второй Event не получает distance первого. Существующие locks сохраняются.
- ProtocolLine: существующий Input и created/updated Impression не меняются.
  delayed_rank_activation сохраняется bool, default false после миграции 047.
- Person: active=true -> false через disable(Impression), PersonDisabled и repository
  update в транзакции. Уже выключенный/отсутствующий прежний Person в cleanup
  не имеет нового перехода. Прямой DisablePerson сохраняет ошибку not found.
- Person.next_rank_activation_on: nullable date из существующей миграции 047,
  используется rebuild service и expiry query.
- Issue evidence: ID, event ID, даты UTC, scope, причина/уверенность, решение,
  regression, deployment action. Исходный request body и PII не сохраняются.
