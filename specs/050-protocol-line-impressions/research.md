# Research: Impressions строк протокола

## Историческое заполнение

Decision: копировать одноимённые пары events через distances без scoped repository.
Preflight с LEFT JOIN выявляет отсутствующие связи и null в исходных четырёх
полях до изменения схемы. Один массовый UPDATE с коррелированными подзапросами
работает на MySQL и SQLite. Типы dateTime/integer соответствуют Event.

Rationale: точный перенос без N+1, загрузки моделей и побочных бизнес-событий.
Alternatives: текущая дата и системный автор искажают историю; перебор моделей
раздувает запросы и может запускать обработчики. JOIN UPDATE ограничивает
переносимость миграционного теста.

## Новые строки

Decision: ProtocolLinesFactory.create принимает обязательный userId; Event.parse
передаёт impression.by. StandardProtocolLinesFactory получает Clock и для каждой
строки создаёт один Impression для начальных created и updated.

Rationale: время появляется в фабрике согласно запросу; автор соответствует
операции, а не первоначальному создателю события. Пустой inputs не вызывает Clock.
Alternatives: передавать готовое время из Application нарушает требование Clock
в фабрике; неявный fallback на Event.created_by скрывает забытые call sites.

## Хранение и переходы

Decision: использовать имеющийся ImpressionCast в persistence mapping переходной
модели, timestamps оставить отключёнными. assignPerson/setPerson/activateRank
сохраняют переданный Impression в updated. Общий lifecycle событий не изменять.

Rationale: минимальный diff с сохранением existing semantics и границ транзакций.
Чистая модель/mapper остаются REF-13; bulk identification остаётся REF-03.

## Проверки

Decision: миграционный тест с отдельной SQLite :memory: connection по образцу
HistoricalEventProtocolBackfillTest, дополнительно полный MySQL suite.
Factory и переходы проверяются в Feature с управляемым Clock и реальным persistence.

## Дополнительный путь создания

Decision: после финального replicate в EloquentUniteEventDataService задать
обоим impressions значение newEvent.created. Оно уже создано фабрикой Event
через Clock с автором текущей операции.
Rationale: replicate удаляет timestamps, но сохраняет старых авторов. Без явной
инициализации новая обязательная схема ломает объединение. Копирование старых
impressions также неверно описывало бы создание новых производных результатов.
Alternatives: ослабление NOT NULL скрывает потерю аудита; отдельная переработка
алгоритма объединения не требуется для совместимости со схемой.
