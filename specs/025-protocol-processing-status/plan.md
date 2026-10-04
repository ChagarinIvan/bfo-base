# Implementation Plan: Статусы обработки протокола этапа

**Branch**: `025-protocol-processing-status` | **Date**: 2026-09-28 | **Spec**: [spec.md](spec.md)

## Summary

Владельцем текущего протокола и его статусов становится `Event`. При создании этапа с протоколом он получает `parsing`; три последовательные фоновые стадии синхронно разбирают файл, идентифицируют строки и пересчитывают разряды. Каждый завершённый переход `Event` публикует событие для запуска следующей стадии после commit. Отдельный `EventProtocol`, очередь `IdentLine` в этом пути и задания пересчёта по одному спортсмену не нужны. Ошибка любой стадии завершает текущий запуск; повторный запуск начинается только после обновления протокола.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript; Laravel 13, Vue 3, PrimeVue.
**Storage**: MySQL/Eloquent; protocol files через `Storage`; asynchronous work через Redis/Horizon.
**Testing**: PHPUnit unit/API/integration, Vitest.
**Project Type**: Web application, backend API and SPA.
**Performance Goals**: UI показывает изменение состояния максимум за 5 секунд; тяжелые этапы не выполняются в HTTP request.
**Constraints**: Обработку запускают domain events after commit; повторная доставка не повторяет завершённый этап. Публичное чтение допускает только готовый Event. Долгоживущий worker не хранит данные идентификации в static cache между заданиями. Каждая стадия сохраняется одной DB-транзакцией под блокировкой строки Event; время worker ограничено 300 секундами, `retry_after` составляет 360 секунд.
**Scale/Scope**: Один текущий протокол на Event; предыдущие протоколы не сохраняются как отдельные processing runs.

## Constitution Check

| Gate | Status | Rationale |
|---|---|---|
| Layering | Accepted feature decision | Application открывает транзакцию и блокирует Event; Event вызывает доменные сервисы через Domain repository ports. `StandardProtocolParser` пока зависит от legacy `ParserFactory` по явному допущению пользователя; перенос адаптера в Infrastructure отложен. |
| No facades in Application/Domain | Pass | Storage, parser, factory, repositories and transaction manager передаются через constructor. |
| Aggregate state/events/audit | Pass | Каждый переход Event меняет `updated`, записывает один соответствующий event и сохраняется repository внутри транзакции. |
| Async after commit | Pass | Создание/замена протокола запускает parsing; `EventParsed` запускает identification; `EventIdentified` запускает один фоновый rank worker. |
| Delivery/idempotency | Partial | Каждая стадия проверяет токен и статус под блокировкой строки Event. Event перехватывает ошибки работы стадии и записывает ошибочный статус в той же транзакции. Повторная доставка терминального события не меняет Event. Принудительная гибель worker до `catch` оставляет переходный статус и требует отдельного решения. |
| Data migration | Pass | Backfill классифицирует все исторические Event и назначает уникальный токен каждому; затем схема делает status/token обязательными. |
| Tests | Pending implementation | Тесты ошибок каждой стадии, очистки, публичной видимости и замены протокола внесены в tasks.md. |

## Design

Уточнение от 2026-10-04 заменяет описанные ниже сценарии повторного выполнения идентификации и пересчёта после ошибки: `EventProcessingFailed` завершает текущую обработку и очищает её результаты. Следующий запуск требует обновления протокола и нового токена. Проверки повторной доставки сохраняются только для защиты от дубликатов и запоздавших задач, а не для возобновления стадии после ошибки.

### Event lifecycle

`Event` хранит статус текущего протокола: `parsing → identifying → rebuildingRanks → ready`, с переходом из рабочих стадий соответственно в `parsingError`, `identifyingError` или `rebuildingRanksError`. Ошибка сохраняет безопасный `errorMessage`; новый протокол очищает сообщение. Статус и `processing_token` обязательны для каждого Event. Фабрика создаёт обычный этап сразу в `parsing` с новым токеном, а объединённый этап с производными результатами — в `ready` с токеном. Команда и событие несут токен; обработчик сверяет его и ожидаемую стадию. Один статус недостаточен: старая задача parsing может увидеть `parsing` нового файла.

`UniteEventsService` сохраняет объединённый Event через обычный `EventRepository::add()`. `Event::create()` не публикует `EventCreated` при начальном статусе `ready`; `EventParsingStarted` публикуется только при `parsing`. `EloquentUniteEventDataService` записывает итоговые дистанции и строки в той же транзакции, но очищает у строк `complete_rank` и `activate_rank`. Разряды из объединённого протокола не присваиваются.

Доменные методы `parse(...)`, `ident(...)` и `updateRanks(...)` проверяют токен и стадию, вызывают доменные сервисы и записывают событие следующего шага. Application открывает транзакцию, блокирует строку Event через `lockById()`, вызывает метод и сохраняет Event. `StandardProtocolLineIdentifier` загружает оставшиеся строки целиком и сохраняет их после цикла; `StandardEventPersonRankUpdater` сохраняет Person по одному. Event перехватывает `Throwable` внутри каждой стадии, записывает соответствующий error-статус, безопасное сообщение и `EventProcessingFailed`. `Event::parse()` сохраняет строки через Domain repository port внутри своего `try`, поэтому ловит и сбой записи. `releasedEvents()` возвращает ожидающие события; успешный `save()` отправляет и удаляет их, поэтому второе сохранение не публикует их повторно.

### Transaction and worker limits

Parsing, identification и пересчёт выполняются в отдельных транзакциях с `SELECT ... FOR UPDATE` строки Event. Внешний lock не используется. `Horizon timeout` для default worker составляет 300 секунд, `queue retry_after` — 360 секунд; worker ограничен 128 MiB памяти. После аварийной гибели worker транзакция откатывается и блокировка БД освобождается. Если попытки очереди исчерпаны без входа в доменный `catch`, Event остаётся в переходном статусе. Из-за чтения всех оставшихся строк identification в память необходимо измерять длительность и память на максимальном реальном файле перед увеличением допустимого объёма.

### Parse flow

После сохранения Event событие `EventParsingStarted` ставит `ParseEventProtocolService` в очередь с `eventId` и `processingToken`. Сервис открывает транзакцию, читает Event через `lockById()`, вызывает `Event::parse()` и сохраняет Event. Доменный метод проверяет токен и стадию, получает содержимое файла, создаёт и сохраняет ProtocolLine через Domain repository port. Строки и переход в `identifying` фиксируются атомарно. При ошибке `Event::parse()` записывает `parsingError` и `EventProcessingFailed` в транзакции стадии; обработчик очищает производные данные после commit. Повторная доставка после перехода не создаёт строк.

`ProtocolParser` — Domain port. Его стандартная реализация работает как adapter над существующими `ParserInterface`/`ParserFactory`: она сохраняет их форматный выбор и преобразует результат в `ProtocolLineInput[]`. `ParserInterface` не меняется. Не создавать новые entry points в `app/Services`.

`Event::parse(token, parser, linesFactory, protocolLines, impression)` получает `ProtocolLineInput[]` через parser port, строит и сохраняет `ProtocolLine[]`, переводит Event в `identifying` и записывает `EventParsed` с immutable `eventId` и `processingToken`. Application service сохраняет Event после завершения метода.

### Identification and rank flow

`EventParsed` запускает `IdentifyProtocolLinesService`. Сервис открывает транзакцию, блокирует Event и проверяет токен через `Event::ident()`. `StandardProtocolLineIdentifier` сначала выполняет быстрое SQL-сопоставление, затем загружает оставшиеся строки, определяет или создаёт спортсменов и сохраняет строки после цикла. `activateEventLines()` обновляет активацию разрядов текущего Event. После успеха Event переходит в `rebuildingRanks` и записывает `EventIdentified`; обработчик следующей стадии запускается после commit. При ошибке Event фиксирует статус и `EventProcessingFailed` в транзакции стадии; обработчик очистки удаляет производные результаты после commit.

`EventIdentified` запускает `UpdateEventRanksService`. Он блокирует Event на время всей транзакции. `StandardEventPersonRankUpdater` получает ID затронутых спортсменов массивом через `ProtocolLineOperations`, блокирует каждого Person, собирает факты, вычисляет разряд и сохраняет его через repository. После полного прохода Event переходит в `ready`. При ошибке Event фиксирует ошибочный статус и событие очистки в той же транзакции. Повторная доставка сверяет токен и стадию и не запускает повторный проход.

Кеш prompts не должен быть static в долгоживущем worker. Путь 025 не использует очередь `IdentLine` и отдельные rank jobs. Загрузка всех оставшихся строк и списка спортсменов ограничена временем и памятью worker; текущий доступный fixture содержит 130 строк. Для большего протокола нужен повторный замер и, при превышении лимита, отдельная задача по порционной обработке с новой моделью согласованности.

### Replacing a protocol and stale events

Уточнение чтения от 2026-10-02: чтения Event сохраняют прежний доступ авторизованного пользователя к активным этапам во всех стадиях обработки. Гостевые list/detail-команды передают `EventResources::readyOnly`; авторизованный фронтенд получает переходные статусы и ошибки для polling. Обычные чтения ProtocolLine и Distance всегда фильтруют `processing_status = ready` вместе с `active`, независимо от пользователя. `readyOnly` удалён из ProtocolLineResources; отдельная проверка Event в `ListEventDistancesService` удалена, список дистанций отсутствующего или скрытого Event пуст. Методы `lock*` сохраняют доступ к активным обрабатываемым данным. Фабрика ProtocolLine ищет существующую Distance через `DistanceRepository::lockOneByCriteria`, чтобы фильтр чтения не создавал дубликаты во время parsing. Условие `completedRank = false` группирует OR, чтобы оно не обходило обязательные фильтры ProtocolLine.

`Event::updateProtocol()` обновляет file, создаёт новый `processing_token`, очищает `error_message`, переводит Event в `parsing` и публикует отдельное событие `EventProtocolUpdated`. После commit `UpdateEventProtocolHandler` вызывает `CleanupEventResultsService`. Сервис под блокировкой Event сверяет токен и стадию `parsing`, удаляет дистанции прежнего протокола (FK каскадно удаляет строки и истории разрядов) и пересчитывает разряды затронутых спортсменов. Затем метод `Event::protocolResultsCleaned()` обновляет `Impression` и публикует `EventProtocolCleaned`; сохранение Event завершает транзакцию очистки. `ParseEventProtocolHandler` слушает это событие и запускает прежний `ParseEventProtocolService` в отдельной транзакции. Спортсмены и общие группы не удаляются. Устаревшее событие или повторная доставка после начала разбора не очищают результаты нового протокола. Identification и rank stage используют прежние обработчики и сверяют токен перед записью. При ошибке любой стадии единое событие `EventProcessingFailed` запускает `DisableEventHandler`, который передаёт команду в `CleanupEventResultsService`. Новый запуск возможен только через обновление протокола.

После любой очистки `Event::protocolResultsCleaned()` вызывает `CupCacheInvalidator`, если Event связан с кубком. Метод записывает `EventProtocolCleaned` для запуска парсинга только если Event активен, токен совпадает и статус равен `parsing`. Repository вызывает `save()` после метода; без изменённых атрибутов Eloquent не выполняет SQL `UPDATE`, но публикует записанное доменное событие.

### Existing data and migration

Одна миграция `2026_09_13_000001_add_event_processing_state.php` добавляет поля в Event, классифицирует исторические записи и назначает каждой уникальный токен без изменения timestamps. Готовые результаты получают `ready`, строки без спортсмена получают `identifyingError`, остальные получают `parsingError`. После заполнения полей миграция делает status/token обязательными и удаляет старую таблицу `protocol_ident_queue`. Миграционный тест проверяет пустую и заполненную БД, а также откат схемы.

### API and SPA

Авторизованные DTO карточки и списка читают `processingStatus` и nullable `errorMessage` непосредственно из Event. Гостевые event/distance/protocol-line запросы фильтруются по `Event.processingStatus = ready`. Колонка списка показывает семь коротких английских меток с разными цветами без сообщения об ошибке. Карточка этапа показывает локализованное сообщение стадии и безопасное сообщение для ошибок, сохраняет loader и detail/list polling с остановкой при terminal state/unmount. Создание сразу возвращает `parsing`; UI не ждёт появления отдельного EventProtocol.

## Project Structure

```text
app/Domain/Event/                     # Event transitions, status, events, parser port
app/Domain/ProtocolLine/              # ProtocolLineInput, line factory/repository port
app/Application/Service/Event/        # Parse, identify and rank orchestration
app/Application/Handler/Event/        # after-commit stage handlers
app/Infrastructure/Laravel/           # ParserInterface adapter and Eloquent repositories
app/Bridge/Laravel/Provider/          # port bindings and queued event integration
database/migrations/                  # replace unpublished run migrations with Event-owned state
resources/spa/                        # existing processing component and polling pages
tests/                                # domain, application, API, integration and SPA coverage (later)
```

## Complexity Tracking

| Deviation | Reason | Follow-up |
|---|---|---|
| Event remains Eloquent-backed | Existing aggregate is the current persistence boundary. | Extracting a plain domain model is separate work. |
| `StandardProtocolParser` imports legacy `ParserFactory` | The user accepted this dependency for 025. | Move the adapter to Infrastructure in a separate change. |
| Identification loads all remaining lines in one transaction | This is the chosen atomic stage model. | Measure larger production protocols; add batching with a new consistency contract if the 300 second or 128 MiB limits are exceeded. |
