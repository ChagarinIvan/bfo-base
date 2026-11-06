# Data model

Person:

| Persistence | Domain | API, authenticated only |
| --- | --- | --- |
| rank_calculated_at, nullable datetime | Carbon|null | rankCalculatedAt, ISO8601 ATOM|null |
| rank_calculation_reason, NOT NULL varchar(32), без default | RankCalculationReason | rankCalculationReason, string |

Enum Unknown=unknown, Manual=manual, EventResultsProcessed=event_results_processed,
EventResultsCleared=event_results_cleared, ProtocolPersonChanged=protocol_person_changed,
Daily=daily, Refill=refill. Protocol=protocol сохранён для исторических данных и очереди.
Backfill старого protocol не выполняется: конкретный сценарий неизвестен.
Domain updateRanks требует причину, одновременно с rank state меняет metadata,
Impression updated и записывает PersonRanksUpdated. Ошибка откатывает committed
состояние. Имя/клуб/disable не являются расчётом и не переписывают metadata.
Дата null и reason unknown для Person без успешного расчёта.
Миграция заполняет старые строки временным default unknown, затем удаляет default.
Person инициализирует Unknown в приложении для всех путей создания.
Дата не равна дате достижения/активации разряда. Единицы точности timestamp проекта,
повторные расчёты могут иметь одинаковую секунду. Сохранение использует текущий
timezone проекта, serializer сохраняет offset ATOM.
