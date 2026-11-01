# Data model

Person:

| Persistence | Domain | API, authenticated only |
| --- | --- | --- |
| rank_calculated_at, nullable datetime | Carbon|null | rankCalculatedAt, ISO8601 ATOM|null |
| rank_calculation_reason, nullable varchar(32) | RankCalculationReason|null | rankCalculationReason, string|null |

Enum Manual=manual, Protocol=protocol, Daily=daily, Refill=refill.
Domain updateRanks требует причину, одновременно с rank state меняет metadata,
Impression updated и записывает PersonRanksUpdated. Ошибка откатывает committed
состояние. Имя/клуб/disable не являются расчётом и не переписывают metadata.
Null/null для новых и старых Person без успешного расчёта, без backfill.
Дата не равна дате достижения/активации разряда. Единицы точности timestamp проекта,
повторные расчёты могут иметь одинаковую секунду. Сохранение использует текущий
timezone проекта, serializer сохраняет offset ATOM.
