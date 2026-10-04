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
**Constraints**: Обработку запускают domain events after commit; повторная доставка не повторяет завершённый этап. Публичное чтение допускает только готовый Event. Долгоживущий worker не хранит данные идентификации в static cache между заданиями. Только parsing сохраняется одной DB-транзакцией; identification и rank stage удерживают внешний lock без общей DB-транзакции.
**Scale/Scope**: Один текущий протокол на Event; предыдущие протоколы не сохраняются как отдельные processing runs.

## Constitution Check

| Gate | Status | Rationale |
|---|---|---|
| Layering | Accepted feature decision; constitution update deferred | Application получает Event и lock; Event вызывает доменные сервисы с Domain repository ports для последовательного сохранения ProtocolLine/Person. Прямой Eloquent в новом доменном коде запрещён. Расхождение с правилом VII об Application-оркестрации persistence принято пользователем; правка конституции относится к отдельной последующей работе и здесь не проводится. |
| No facades in Application/Domain | Pass | Storage, parser, factory, repositories and transaction manager передаются через constructor. |
| Aggregate state/events/audit | Pass | Каждый переход Event меняет `updated`, записывает один соответствующий event и сохраняется repository внутри транзакции. |
| Async after commit | Pass | Создание/замена протокола запускает parsing; `EventParsed` запускает identification; `EventIdentified` запускает один фоновый rank worker. |
| Delivery/idempotency | Operational tradeoff | Все write paths получают один lock на Event; статус и токен текущего протокола сверяются после захвата. TTL больше максимального времени job, lock освобождается в finally, владение проверяется перед записью. Проверка не защищает промежуток до самой записи при истечении TTL; этот остаточный риск принят пользователем. Повторная доставка после ошибки не возобновляет стадию. |
| Data migration | Required | Не применять черновые миграции `EventProtocol`: заменить их схемой полей Event и backfill `ready` для существующих завершённых результатов. Данных active run и старого `failed` для переноса нет. |
| Tests | Pending implementation | Тесты ошибок каждой стадии, очистки, публичной видимости и замены протокола внесены в tasks.md. |

## Design

Уточнение от 2026-10-04 заменяет описанные ниже сценарии повторного выполнения идентификации и пересчёта после ошибки: `EventProcessingFailed` завершает текущую обработку и очищает её результаты. Следующий запуск требует обновления протокола и нового токена. Проверки повторной доставки сохраняются только для защиты от дубликатов и запоздавших задач, а не для возобновления стадии после ошибки.

### Event lifecycle

`Event` хранит статус текущего протокола: `parsing → identifying → rebuildingRanks → ready`, с переходом из рабочих стадий соответственно в `parsingError`, `identifyingError` или `rebuildingRanksError`. Ошибка сохраняет безопасный `errorMessage`; новый протокол очищает сообщение. Статус и `processing_token` обязательны для каждого Event. Фабрика создаёт обычный этап сразу в `parsing` с новым токеном, а объединённый этап с производными результатами — в `ready` с токеном. Команда и событие несут токен; обработчик сверяет его и ожидаемую стадию. Один статус недостаточен: старая задача parsing может увидеть `parsing` нового файла.

`UniteEventsService` сохраняет объединённый Event через обычный `EventRepository::add()`. `Event::create()` не публикует `EventCreated` при начальном статусе `ready`; `EventParsingStarted` публикуется только при `parsing`. `EloquentUniteEventDataService` записывает итоговые дистанции и строки в той же транзакции, но очищает у строк `complete_rank` и `activate_rank`. Разряды из объединённого протокола не присваиваются.

Доменные методы `parse(...)`, `ident(...)`, `updateRanks(...)` и `failProcessing(...)` проверяют допустимость стадии, меняют `updated`, фиксируют переход Event и событие следующего шага. `failProcessing(...)` выводит конкретный error-статус из текущей стадии и принимает только безопасный пользовательский текст; технические детали остаются в логах. `Event::ident()` и `Event::updateRanks()` содержат последовательные циклы и вызывают доменные сервисы через интерфейсы. Сервисы используют Domain repository ports: стандартная реализация может принять `ProtocolLineRepository`, `PersonRepository` и связанные порты; Eloquent-реализации портов остаются в Infrastructure. Каждая обработанная ProtocolLine или Person сохраняется сервисом до следующей итерации. Application получает lock, загружает Event, вызывает его метод и сохраняет итоговое состояние Event. Контракт status API сохраняет поле `processingStatus`.

### External Event lock

Замена протокола, identification и ranks используют один ключ `event-protocol:{eventId}` через Domain port `EventProcessingLock`. Infrastructure adapter использует Redis-backed Symfony Lock. Parsing выполняется один раз в DB-транзакции под блокировкой строки Event через `lockById`, без внешнего lease; проверка токена и статуса под этой блокировкой отсеивает устаревшую доставку. Строки и переход стадии сохраняются атомарно. Ошибка parsing сразу переводит Event в `parsingError` в отдельной транзакции под той же блокировкой строки, без повтора очереди. Для внешнего lock TTL больше жёсткого timeout worker; `retry_after` больше worker timeout. В identification и ranks владение проверяется перед сохранением, потеря lock прерывает работу. Проверка владения не защищает атомарно от истечения TTL между проверкой и записью; этот остаточный риск принят.

### Parse flow

После сохранения Event событие `EventParsingStarted` ставит `ParseEventProtocolService` в очередь с `eventId` и `processingToken`. Сервис открывает транзакцию, читает Event через `lockById()`, проверяет токен и `parsing`, получает содержимое текущего файла, вызывает `Event::parse()`, сохраняет полученные ProtocolLine через `ProtocolLineRepository::add(...$lines)` и обновляет Event. Строки и переход в `identifying` фиксируются атомарно. При ошибке транзакция откатывается, затем под блокировкой той же строки сразу записывается `parsingError`. Повторная доставка после перехода не создаёт строк.

`ProtocolParser` — Domain port. Его стандартная реализация работает как adapter над существующими `ParserInterface`/`ParserFactory`: она сохраняет их форматный выбор и преобразует результат в `ProtocolLineInput[]`. `ParserInterface` не меняется. Не создавать новые entry points в `app/Services`.

`Event::parse(parser, linesFactory, content, extension, impression)` получает `ProtocolLineInput[]` через parser port, строит `ProtocolLine[]` через factory, переводит Event в `identifying` и записывает `EventParsed` с immutable `eventId` и `processingToken`. Application service сохраняет возвращённые строки и Event атомарно через repositories.

### Identification and rank flow

Обработчик `EventParsed` запускает один `IdentifyProtocolLinesService` в фоне. Сервис захватывает внешний lock, получает Event через `byId()` и проверяет токен/стадию, затем вызывает `Event::ident($identifier, $impression)`. Внутри метода последовательно выполняются быстрое сопоставление и полное сопоставление оставшихся строк через доменный identifier. Он читает строки текущего Event без `person_id` ограниченными порциями через `ProtocolLineRepository`, определяет или создаёт спортсмена через доменные порты и сохраняет каждую ProtocolLine до перехода к следующей. Быстрый поиск может вычислить кандидатов пакетом, но не выполняет bulk update строк. Доменный сервис получает проверку владения lock и токена через узкий интерфейс и прекращает цикл при их потере. Общей транзакции нет; при зафиксированной ошибке обработчик удаляет результаты протокола, и новый запуск требует обновления протокола. После запроса, подтверждающего отсутствие неопознанных строк, `Event::ident()` переводит Event в `rebuildingRanks` с `EventIdentified`; Application сохраняет Event и запускает следующую стадию после успешного сохранения. Пользовательский endpoint или CLI для ручного возобновления не добавляется.

Обработчик `EventIdentified` запускает один `UpdateEventRanksService` в фоне. Сервис захватывает внешний lock, получает Event через `byId()`, проверяет токен/стадию и вызывает `Event::updateRanks($rankUpdater, $impression)`. Доменный rank updater через `ProtocolLineRepository` выбирает уникальные `person_id` текущего Event ограниченными порциями, получает факты, вызывает calculator и `Person::updateRanks()`, затем сохраняет Person через `PersonRepository` до следующей итерации. Он проверяет владение lock и токен перед каждым сохранением. Общей транзакции нет; атомарное сохранение одного Person остаётся допустимым. Event фиксирует `ready` и событие завершения только после успешного прохода всех затронутых спортсменов. Пересчёт вычисляет состояние из текущих сохранённых фактов и не создаёт накопительных результатов; при зафиксированной ошибке обработчик очищает результаты протокола. Задания на каждого спортсмена, rank batch и счётчики завершения удаляются из цепочки 025.

Кеш prompts и нормализованных имён ограничен одним вызовом идентификации; `static` кеш в Horizon worker не используется. Поиск ближайшего совпадения не накапливает коллекцию оценок для каждого имени. Строки и спортсмены читаются ограниченными порциями, а `person_id` дедуплицируется до пересчёта. Размер порции и таймауты выбираются после замера самого большого доступного протокола. Глобальные legacy-команды simple/big/queue identification сейчас работают по всем строкам; до включения нового pipeline их расписание удаляется либо их выборка исключает transitional Event, иначе они могут менять те же строки без Event lock.

Уточнение identification от 2026-10-02: после сохранения всех назначений спортсменов `StandardProtocolLineIdentifier` вызывает `ProtocolLineOperations::activateEventLines(Event)`. Infrastructure выполняет один SQL UPDATE с JOIN: только строки текущего Event с определённым спортсменом, выполненным КМС/МС и пустым `activate_rank`, при наличии активированного выполнения того же разряда у того же спортсмена в более раннем Event. Дата активации берётся из текущего Event; существующая дата сохраняется. Вызов охватывает и строки быстрого SQL-сопоставления, даже если цикл полного сопоставления пуст. Dry-run отсутствует.

Пакетное обновление без вызова `ProtocolLine::activateRank()` и без событий на каждую строку принято пользователем как исключение из aggregate mutation path: этот шаг входит в identification, его завершение фиксируется `EventIdentified` и `Event.updated`, а общий пересчёт начинается после commit. `ProtocolLineRepository::update()` принимает несколько строк и сохраняет каждую модель через `save()`; identifier передаёт все обработанные строки после цикла. Эта правка заменяет прежнее требование сохранять назначение перед следующей итерацией.

Уточнение rank stage от 2026-10-02: контракт `Event::updateRanks(token, EventPersonRankUpdater, Impression): void` оформляется по образцу `parse()` и `ident()`. После проверки токена и стадии Event вызывает `EventPersonRankUpdater::update(Event, Impression): void`, при успехе переходит в `ready` и записывает `EventRanksUpdated`, при `RanksUpdatingError` сохраняет `rebuildingRanksError`, сообщение и `EventProcessingFailed`; `updated` меняется в `finally`. Прежние параметры lease и token доменного updater в этом контракте удалены. На этом шаге добавляется только интерфейс updater, реализация пересчёта и Application-оркестрация остаются отдельной работой.

Следующий шаг rank stage: `UpdateEventRanksHandler` обрабатывает `EventIdentified` через `ShouldQueueAfterCommit`, формирует `UpdateEventRanks(eventId, processingToken, impression)` и вызывает `UpdateEventRanksService`. Сервис по образцу текущего `IdentifyProtocolLinesService` открывает DB-транзакцию, получает Event через `lockById()`, формирует Impression с текущим временем и исходным актором, вызывает `Event::updateRanks()` и сохраняет Event через repository. Реализация `EventPersonRankUpdater` остаётся отдельной работой; сервис пока использует только доменный интерфейс.

Реализация `StandardEventPersonRankUpdater` получает ID затронутых спортсменов через
`ProtocolLineOperations`, блокирует каждого через `PersonRepository`, собирает факты
`RankFactsCollector`, вычисляет состояние `RankCalculator` на дату `Impression` стадии и
сохраняет `Person::updateRanks()` через repository. Отсутствующий спортсмен даёт безопасный
`RanksUpdatingError`. Текущий `UpdateEventRanksService` проводит весь проход в одной
транзакции, а `personIdsForEvent()` возвращает массив. Это расходится с ранним планом
порционной обработки без общей транзакции; изменение этих контрактов требует отдельной
доработки Application-сценария и порта чтения.

### Replacing a protocol and stale events

Уточнение чтения от 2026-10-02: чтения Event сохраняют прежний доступ авторизованного пользователя к активным этапам во всех стадиях обработки. Гостевые list/detail-команды передают `EventResources::readyOnly`; авторизованный фронтенд получает переходные статусы и ошибки для polling. Обычные чтения ProtocolLine и Distance всегда фильтруют `processing_status = ready` вместе с `active`, независимо от пользователя. `readyOnly` удалён из ProtocolLineResources; отдельная проверка Event в `ListEventDistancesService` удалена, список дистанций отсутствующего или скрытого Event пуст. Методы `lock*` сохраняют доступ к активным обрабатываемым данным. Фабрика ProtocolLine ищет существующую Distance через `DistanceRepository::lockOneByCriteria`, чтобы фильтр чтения не создавал дубликаты во время parsing. Условие `completedRank = false` группирует OR, чтобы оно не обходило обязательные фильтры ProtocolLine.

`Event::updateProtocol()` обновляет file, создаёт новый `processing_token`, очищает `error_message`, переводит Event в `parsing` и публикует отдельное событие `EventProtocolUpdated`. После commit `UpdateEventProtocolHandler` вызывает `CleanupEventResultsService`. Сервис под блокировкой Event сверяет токен и стадию `parsing`, удаляет дистанции прежнего протокола (FK каскадно удаляет строки и истории разрядов) и пересчитывает разряды затронутых спортсменов. Затем метод `Event::protocolResultsCleaned()` обновляет `Impression` и публикует `EventProtocolCleaned`; сохранение Event завершает транзакцию очистки. `ParseEventProtocolHandler` слушает это событие и запускает прежний `ParseEventProtocolService` в отдельной транзакции. Спортсмены и общие группы не удаляются. Устаревшее событие или повторная доставка после начала разбора не очищают результаты нового протокола. Identification и rank stage используют прежние обработчики и сверяют токен перед записью. При ошибке любой стадии единое событие `EventProcessingFailed` запускает `DisableEventHandler`, который передаёт команду в `CleanupEventResultsService`. Новый запуск возможен только через обновление протокола.

После любой очистки `Event::protocolResultsCleaned()` вызывает `CupCacheInvalidator`, если Event связан с кубком. Метод записывает `EventProtocolCleaned` для запуска парсинга только если Event активен, токен совпадает и статус равен `parsing`. Repository вызывает `save()` после метода; без изменённых атрибутов Eloquent не выполняет SQL `UPDATE`, но публикует записанное доменное событие.

### Existing data and draft migrations

Миграции `2026_09_13_000001_create_event_protocols_table.php` и `2026_09_13_000002_backfill_historical_event_protocols.php` относятся к ещё не применённому черновому варианту. Первая добавляет поля `events.processing_status`, `events.processing_token` и nullable `events.error_message`; вторая помечает завершённые результаты `ready`. Завершающая миграция `2026_09_30_000002_require_event_processing_state.php` назначает каждому существующему Event токен и статус, затем делает первые два поля обязательными. Неполные данные получают соответствующую ошибку обработки и остаются закрыты для гостя. Объединённые этапы с готовыми производными строками получают `ready`. Миграции сохраняют строки и timestamps. Новых Event без протокола создавать нельзя.

### API and SPA

Авторизованные DTO карточки и списка читают `processingStatus` и nullable `errorMessage` непосредственно из Event. Гостевые event/distance/protocol-line запросы фильтруются по `Event.processingStatus = ready`. UI показывает подписи семи статусов и безопасное сообщение для ошибок, использует локализованный текст стадии при пустом сообщении, сохраняет loader и detail/list polling с остановкой при terminal state/unmount. Создание сразу возвращает `parsing`; UI не ждёт появления отдельного EventProtocol.

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

| Deviation | Why needed | Follow-up |
|---|---|---|
| Event remains Eloquent-backed and Domain model therefore is not framework-free | Existing aggregate model is the current persistence boundary and this change should not expand the refactor to all aggregates | Keep new parsing behind Domain ports; track extracting Event as a plain domain object separately. |
| Domain services persist ProtocolLine and Person through repository ports | The Event method owns the synchronous processing loops while each completed item must survive a worker crash | Keep repository interfaces in Domain and Eloquent adapters in Infrastructure; Application owns the external lock and saves Event transitions. The user deferred the corresponding Constitution VII update to separate work. |
| Identification and ranks use an external lock without an overall DB transaction | A transaction over the whole stage would roll back progress on worker failure and hold DB locks for the entire process | Persist each line/person before the next, set TTL above bounded job timeout, release in finally, verify ownership and token, keep Event transitional until completion. The check/write expiry race remains an accepted operational risk. |
