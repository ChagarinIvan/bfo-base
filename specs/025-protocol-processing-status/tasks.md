# Tasks: Статусы обработки протокола этапа

**Input**: [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), [contracts/event-processing-status.md](contracts/event-processing-status.md), [quickstart.md](quickstart.md)

**Scope**: Это текущий список работ после решения передать жизненный цикл протокола агрегату Event. Старый список сохранён в [tasks-event-protocol-history.md](tasks-event-protocol-history.md) только как история; его задачи не выполняются по прежнему плану.

**Tests**: Изменения поведения требуют Domain/Application unit и integration/API request tests; SPA polling и статусы требуют Vitest.

## Phase 1: Setup

- [X] T001 Зафиксировать текущие вызовы EventProtocol, IdentLine и rank jobs, а также точки запуска из CLI и очереди в `app/Application/`, `app/Bridge/Laravel/`, `app/Services/` и `tests/`; составить список удаления и совместимости в `specs/025-protocol-processing-status/research.md`.

## Phase 2: Foundational

- [X] T002 Заменить ещё не применённые миграции `2026_09_13_000001_create_event_protocols_table.php` и `2026_09_13_000002_backfill_historical_event_protocols.php` схемой Event-owned status/token/error; завершающей миграцией назначить историческим Event статус и токен, затем сделать status/token обязательными. Проверить пустую и существующую базу в `tests/Feature/Event/HistoricalEventProtocolBackfillTest.php`.
- [X] T003 Покрыть переходы Event, три error-статуса по текущей стадии, установку/очистку `errorMessage`, смену токена, повторную доставку и запоздалую стадию unit-тестами в `tests/Domain/Event/EventTest.php`; перенести актуальные сценарии из `tests/Domain/Event/EventProtocolTest.php`.
- [X] T004 Перенести статусы, токен, audit impression и immutable stage events в `app/Domain/Event/Event.php` и `app/Domain/Event/Event/`; удаление старого агрегата и repository отложить до переключения потребителей в T018.
- [X] T005 Адаптировать `ProtocolParser` и `ProtocolLinesFactory` к Event без eventProtocolId; добавить `symfony/lock` в `composer.json`/`composer.lock`, Domain port EventProcessingLock и Redis adapter с длительным TTL, освобождением в `finally`, проверкой владения и возможностью refresh в `app/Domain/Event/`, `app/Domain/ProtocolLine/Factory/`, `app/Infrastructure/Laravel/` и `app/Bridge/Laravel/Provider/`; согласовать timeout Horizon/очереди в `config/horizon.php` и `config/queue.php` так, чтобы TTL > worker timeout и `retry_after` > worker timeout; сохранить parser fixtures.

## Phase 3: User Story 1 — Видеть обработку нового протокола (P1)

**Goal**: Event сразу показывает parsing и последовательно проходит фоновые стадии до идентификации.

**Independent Test**: создание Event с файлом даёт parsing; после commit фоновые стадии записывают строки и показывают identifying, а зафиксированная ошибка идентификации удаляет результаты неудачного протокола.

- [X] T006 [US1] Написать integration-тесты создания/замены, старого токена, однократного parsing, атомарной записи строк и немедленной ошибки в `tests/Feature/Event/ProcessEventProtocolTest.php` и `tests/Application/Handler/Event/ProcessEventProtocolHandlerTest.php`.
- [X] T007 [US1] Создавать Event сразу с `parsing` и токеном через фабрику; после сохранения запускать parsing, замена получает внешний Event lock в `app/Application/Service/Event/AddEventService.php`, `app/Application/Service/Event/UpdateEventService.php`, `app/Application/Handler/Event/` и `app/Bridge/Laravel/Provider/Event/EventProvider.php`; отключить глобальные legacy-ident задания.
- [X] T008 [US1] Реализовать parsing под DB row lock и одной транзакцией с проверкой токена, `Event::parse()`, сохранением строк и переходом в identifying; ошибка сразу фиксируется как `parsingError` после отката в `app/Application/Service/Event/ParseEventProtocolService.php`, `app/Domain/Event/Event.php` и `app/Infrastructure/Laravel/Eloquent/ProtocolLine/`.
- [X] T009 [US1] Написать тесты быстрого и полного сопоставления, создания отсутствующего спортсмена, сохранения каждой строки без общей транзакции, падения после части строк, повторной/одновременной доставки и потери внешнего lock в `tests/Feature/Event/IdentifyEventProtocolTest.php` и `tests/Domain/ProtocolLine/`.
- [X] T010 [US1] Перенести алгоритм из `app/Services/ProtocolLineIdentService.php` и `app/Bridge/Laravel/Console/Commands/IdentProtocolLineCommand.php` в доменный identifier в `app/Domain/ProtocolLine/` и цикл `Event::ident()` в `app/Domain/Event/Event.php`; `IdentifyProtocolLinesService` в `app/Application/Service/Event/` получает внешний lock до `byId()` и вызывает Event. Доменный identifier через `ProtocolLineRepository` и порты Person/Prompt читает текущие строки без person_id порциями, определяет или создаёт спортсмена, сохраняет каждую строку до следующей без bulk update/общей транзакции, проверяет lock/token, не использует IdentLine, static prompt cache и прямой Eloquent.
- [X] T011 [US1] После подтверждения отсутствия строк без person_id завершить `Event::ident()` переходом в rebuildingRanks и сохранить Event с immutable EventIdentified для запуска следующей стадии после сохранения в `app/Domain/Event/Event.php`, `app/Application/Service/Event/` и `app/Application/Handler/Event/`.

## Phase 4: User Story 2 — Понимать итог и сбой обработки (P1)

**Goal**: готовность подтверждается полным пересчётом разрядов, ошибка останавливает ожидание.

**Independent Test**: rank stage обрабатывает каждого затронутого спортсмена в одном фоне, повтор после прерывания безопасен, Event остаётся transitional до завершения.

- [X] T012 [US2] Написать integration-тесты уникального набора спортсменов, сохранения каждого без общей транзакции, прерывания rank stage, повтора, потери lock, замены протокола во время работы и перехода ready в `tests/Feature/Event/UpdateEventRanksTest.php`.
- [X] T013 [US2] Реализовать один фоновый UpdateEventRanksService, который получает внешний lock до `byId()` и вызывает `Event::updateRanks()` в `app/Domain/Event/Event.php`; доменный rank updater в `app/Domain/Person/` через `ProtocolLineRepository`, calculator и `PersonRepository` последовательно обходит уникальных person_id, обновляет и сохраняет каждого до следующего шага без общей транзакции и прямого Eloquent, проверяет token и владение lock, ограничивает память и только после полного прохода выставляет ready; убрать per-person rank jobs из цепочки 025 в `app/Application/Service/Event/` и `app/Application/Handler/Event/`.
- [X] T014 [US2] Ошибка каждой стадии завершает текущую обработку без повторного запуска стадии; сохранить соответствующий error-статус и `errorMessage`, отправить `EventProcessingFailed`, проверить три стадии и очистку сообщения при обновлении протокола.
- [X] T015 [US2] Переключить event DTO, публичные event/distance/protocol-line запросы и гостевые фильтры на `Event.processing_status` в `app/Application/Dto/Event/`, `app/Application/Service/`, `app/Infrastructure/Laravel/Eloquent/`; вернуть nullable `errorMessage` только авторизованным клиентам; проверить `ready`, переходные и все три ошибочных состояния, отсутствие сообщения в гостевых ответах в `tests/Feature/Api/V1/`.

## Phase 5: User Story 3 — Контролировать обработку в списке этапов (P2)

**Goal**: список и карточка показывают стадии текущего Event без отдельного run.

**Independent Test**: API возвращает актуальный processingStatus, список опрашивает только переходные этапы и прекращает запросы после завершения.

- [X] T016 [US3] Обновить union `processingStatus` и nullable `errorMessage` в `resources/spa/api/types.ts`, локализовать подписи трёх error-статусов и fallback в `resources/lang/by.json`, показать конкретный статус и сообщение в `resources/spa/components/EventProcessingStatus.vue`; проверить создание сразу в parsing и текущий polling в `resources/spa/pages/events/EventViewPage.vue` и `resources/spa/pages/competitions/CompetitionDetailsPage.vue`; покрыть API и Vitest в `tests/Feature/Api/V1/Event/` и `resources/spa/pages/competitions/CompetitionDetailsPagePolling.test.ts`.

## Phase 6: User Story 4 — Не тратить запросы после ухода со страницы (P3)

**Goal**: переходные страницы прекращают polling при закрытии, готовности и ошибке.

**Independent Test**: после unmount новых запросов нет, временный сбой не останавливает следующий опрос.

- [X] T017 [US4] Проверить повторный запрос после временного сбоя и остановку polling после `ready` и каждого из трёх error-статусов, а также после unmount в `resources/spa/pages/events/EventViewPage.test.ts` и `resources/spa/pages/competitions/CompetitionDetailsPagePolling.test.ts`; исправить компоненты по результатам.

## Phase 7: Polish

- [X] T018 После переключения потребителей удалить EventProtocol и старые repository/handlers; проверить оставшиеся usages IdentLine и перенести функции вне 025 до удаления `app/Models/IdentLine.php`, `app/Bridge/Laravel/Console/Commands/IdentProtocolLineCommand.php`, `app/Bridge/Laravel/Console/Commands/StartBigIdentCommand.php` и таблицы; обновить `app/Bridge/Laravel/Console/Kernel.php` и соответствующие tests.
- [X] T019 Замерить parsing transaction, максимальное время каждой стадии, время удержания внешнего lock, число запросов и пик памяти на крупнейшем доступном протоколе; проверить TTL > hard worker timeout и `retry_after` > worker timeout, освобождение lock после нормального завершения, повторить две обработки одним worker и проверить отсутствие stale cache/lock; результаты и принятый риск истечения TTL между check/write записать в `specs/025-protocol-processing-status/quickstart.md`.
- [X] T019a Перенести нормализацию полей строки в преобразование результата парсера в `ProtocolLineInput`; приватный метод `StandardProtocolLinesFactory` создаёт модель из подготовленных атрибутов. Проверить разбор реального fixture и путь legacy `ProtocolLineService`.
- [ ] T020 Запустить узкие PHP/Vitest проверки, затем один раз финальные `composer test`, `composer stan`, `composer cs`, `composer rector -- --dry-run`, frontend CI и запуск приложения; сверить все acceptance scenarios, контракт и checklist из `specs/025-protocol-processing-status/`.

- [X] T021 Реализовать `EventPersonRankUpdater` через доменные порты и `Person::updateRanks()`; проверить сохранение нескольких спортсменов, вычисление ранга и отсутствие спортсмена.
- [ ] T022 Отдельно устранить расхождение с планом порционной обработки без общей транзакции в `UpdateEventRanksService` и `ProtocolLineOperations`.
- [X] T023 Согласовать SPA создания и просмотра этапа с фактическим контрактом `processingStatus`/`errorMessage`: три статуса ошибки, локализованные сообщения, скрытие пустых результатов до готовности, загрузка дистанций после готовности и polling карточки/списка; покрыть сценарий создания и завершения обработки Vitest.
- [X] T024 Обновить `Event::updateProtocol()` для нового токена и `parsing`, запустить прежнюю цепочку через отдельное событие `EventProtocolUpdated` и `UpdateEventProtocolHandler`; перед parsing удалить старые дистанции/строки, пересчитать разряды прежних участников и отвергнуть старый токен до очистки. Проверить замену и полный переход до `ready` интеграционным тестом.
- [X] T025 Вернуть единое `EventProcessingFailed` в обработчик очистки для ошибки любой стадии; проверять токен после commit до удаления данных, покрыть очистку текущего протокола и защиту нового протокола от запоздалой ошибки. Зафиксировать, что новый запуск возможен только после обновления протокола.
- [X] T026 Перенести блокировку Event, проверку токена, удаление производных результатов и пересчёт разрядов из `DisableEventHandler`/trait в `CleanupEventResultsService`; оставить обработчик преобразователем обоих событий в одну команду. Для внутренней очистки отключённого Event использовать флаг `includeInactive` в `EventRepository::lockById()` с прежним фильтром по умолчанию. Отсутствующий Event завершает сценарий `EventNotFound`. Проверить отключение, ошибку и запоздавшую ошибку интеграционными тестами.
- [X] T027 Убрать очистку из `ParseEventProtocolService`: `EventProtocolUpdated` запускает `CleanupEventResultsService`, метод Event после очистки выпускает `EventProtocolCleaned`, а `ParseEventProtocolHandler` запускает прежний разбор после commit. Покрыть порядок стадий и повторную доставку интеграционным тестом.
- [X] T028 Вернуть `Event::protocolResultsCleaned(): void` и безусловное сохранение Event после очистки. Доменный метод решает, нужно ли публиковать `EventProtocolCleaned` для парсинга и вызывать `CupCacheInvalidator` для сброса кеша кубков. Проверить ошибку, выключение и сброс кеша интеграционным тестом.
- [X] T029 Сохранять объединённый Event сразу в `ready` без `EventCreated` и стадий обработки. При заполнении производных строк не переносить `complete_rank` и `activate_rank`; проверить отсутствие нового факта разряда интеграционным тестом.

## Dependencies & execution order

`T001 → T002–T005 → T006–T011 → T012–T015 → T016–T017 → T018–T020`.
US2 зависит от подтверждённой идентификации US1; US3/US4 используют те же статусы, но их SPA-тесты могут идти отдельно после фиксации API-контракта. MVP: US1 с корректным переходом в rank stage; публичное включение результата требует US2.

## Parallel opportunities

T003 и T005 можно готовить параллельно после фиксации схемы T002. T016 и T017 затрагивают разные страницы и могут идти параллельно после стабилизации status API. Остальные задачи сохраняют порядок из-за общих файлов Event и миграций.
