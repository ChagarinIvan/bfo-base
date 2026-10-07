# Архитектура бэкенда BFO Base

**Статус**: обязательное руководство для новых серверных фич и PR review.
**Снимок кода**: 2026-09-27. **Спецификация**: `specs/040-backend-architecture-manifest/`.

## Как применять документ

Конституция `.specify/memory/constitution.md` устанавливает норму и имеет приоритет. Этот манифест поясняет норму на коде, отмечает переходные исключения и ведёт очередь ограниченных рефакторингов. Наблюдение о существующем коде не разрешает повторять его в новой фиче. Если фича затрагивает переходный участок, зафиксируйте в её `spec.md` выбранную границу, тесты и план устранения долга.

Рабочие решения фичи 040: целевой Domain не зависит от Eloquent; наблюдаемый бизнес-переход агрегата меняет `updated` и записывает событие; бизнес-сущности с `active` выключаются через `disable()`. Технический импорт и пересчёт классифицируются отдельно. Эти решения записаны в `specs/040-backend-architecture-manifest/spec.md` и конституции 2.6.0.

## Карта бэкенда

| Область | Роль | Текущий код | Целевая граница |
| --- | --- | --- | --- |
| Bridge | HTTP, маршруты, аутентификация, сериализация, провайдеры, консоль | `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`, `app/Bridge/Laravel/Http/Controllers/ApiAction.php` | Преобразует внешний запрос в command, не принимает бизнес-решений. |
| Application | Use cases, команды, DTO, assemblers, handlers | `app/Application/Service`, `app/Application/Dto`, `app/Application/Handler` | Координирует транзакцию и порты, не хранит бизнес-правила и SQL. |
| Domain | Агрегаты, value objects, политики, repository ports | `app/Domain/Event`, `Person`, `Cup`, `ProtocolLine`, `RankCheck`, `Shared` | Не зависит от Laravel, транспорта и адаптеров. |
| Infrastructure | Eloquent repositories, транзакции, Redis cache, внешние интеграции | `app/Infrastructure/Laravel/Eloquent`, `app/Infrastructure/Laravel/Cache`, `app/Infrastructure/Integration` | Реализует порты, не подменяет бизнес-переход прямым update. |
| Legacy | Старые сервисы и SQL-репозиторий | `app/Services` (4 файла), `app/Repositories` (1 файл), части `app/Models` | Сдерживается и удаляется ограниченными шагами; новый код туда не добавляется. |

Фактическая граница Domain пока нарушена: `app/Domain/Shared/AggregatedModel.php` наследует Eloquent `Model`; `app/Domain/Shared/AggregatedEvent.php` использует Laravel event traits; агрегаты импортируют `app/Infrastructure/Laravel/Eloquent/Auth/ImpressionCast.php`; `app/Domain/Auth/User.php`, `app/Domain/Distance/Distance.php` и `app/Domain/RankCheck/RankCheckRow.php` также являются framework-моделями. В Domain есть зависимости от `App\Models\Year`, Laravel Collection и legacy классов. Это карта миграции, а не новый допустимый шаблон.

Предметные области: соревнования и события (`Competition`, `Event`, `Distance`, `Group`), участники (`Person`, `Club`, `PersonPrompt`, `PersonPayment`), строки протокола и идентификация (`ProtocolLine`), разряды и проверки (`Rank`, `RankCheck`), кубки (`Cup`, `CupEvent`, `CupType`, `CupTable`). Связи между областями видны в портах `app/Domain/*/*Repository.php` и обработчиках `app/Application/Handler`.

## Путь новой фичи

### Запись

`ApiV1RoutesServiceProvider` направляет запрос в action. Action принимает валидируемый DTO и `UserId`, создаёт command и вызывает `ApplicationService::execute(Command)`. Пример: `app/Bridge/Laravel/Http/Controllers/Api/V1/Event/UpdateEventAction.php:16-25`.

Application service открывает `TransactionManager::run`, получает и при необходимости блокирует агрегат через порт, создаёт `Impression(Clock::now(), userId)`, вызывает намеренный метод агрегата, сохраняет через repository и собирает ответ через assembler. Пример: `app/Application/Service/Event/UpdateEventService.php:36-58`; adapter транзакции: `app/Infrastructure/Laravel/Eloquent/Shared/EloquentTransactionalManager.php:11-21`. Ошибка доменного правила переходит в ожидаемую application error с HTTP-контрактом, если сценарий вызван из API.

Агрегат решает, допустим ли переход, меняет своё состояние и записывает событие. `Event::updateInfo()` в `app/Domain/Event/Event.php:83-90` соответствует этому подходу. Repository port в Domain скрывает Eloquent adapter. `app/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepository.php:59-62` вызывает `save()` текущей переходной модели. В целевой архитектуре жизненный цикл событий не должен зависеть от Eloquent `Model::save()`; выделение чистого агрегата делается отдельной фичей, сохраняя контракт сценария.

### Чтение и HTTP-контракт

Сценарий чтения задаёт отбор через `Criteria` и требуемые связи через типизированный `*Resources`; repository делает фильтрацию, eager loading и пагинацию через `Slice`. Примеры: `app/Domain/Event/EventResources.php`, `app/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepository.php:24-35,83-96`, `app/Application/Service/Competition/ListCompetitionsService.php`. Не используйте Criteria как скрытый флаг загрузки связей и не загружайте весь список для постраничного API.

`ApiAction` в `app/Bridge/Laravel/Http/Controllers/ApiAction.php:35-120` централизует validation DTO, ожидаемые application errors, сериализацию, статус, pagination headers и CSV attachment. Action не собирает обычный `JsonResponse` вручную. `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/ExportCupTableAction.php:18-26` показывает специализированный ответ через `$this->csv()`.

### События и побочные эффекты

Сегодня агрегат вызывает `recordThat()`, а `AggregatedModel::save()` после `parent::save()` вызывает Laravel `event()` (`app/Domain/Shared/AggregatedModel.php:15-37`). `EventHandlerServiceProvider` автоматически обнаруживает обработчики в `app/Application/Handler/{Event,Cup,Person,PersonPrompt,ProtocolLine,Rank,RankCheck}` (`app/Bridge/Laravel/Provider/EventHandlerServiceProvider.php:11-26`). Обработчики запускают протокол, пересчёт разрядов, выключение связанных данных, очистку кэша и создание подсказок.

Сохранение находится внутри транзакции Application, поэтому публикация сейчас происходит **до её commit**. Обработчик, читающий сохранённое состояние, должен стартовать после commit. Очередные обработчики Person, Event и Cup, которые ранее использовали `ShouldQueue`, переведены на `ShouldQueueAfterCommit` после ошибки восстановления только что созданного Person в Horizon. В новых обработчиках передавайте достаточно неизменяемых фактов или ID и проектируйте повторную доставку как возможную. События с целой Eloquent-моделью, например `app/Domain/Event/Event/EventInfoUpdated.php`, при обработке могут видеть повторно загруженное состояние, отличное от момента записи.

В фиче 025 `AggregatedModel` разделил ожидающие публикации события и историю `releasedEvents()`: повторный `save()` не отправляет прежние события. Сбой сохранения не публикует событие. `ParseEventProtocolService` записывает `failed` при ошибке разбора; политику повторов системного сбоя и наблюдаемость очереди требуется проверить отдельно.

### Импорт и протоколы

`EventProtocolUpdated` запускает `UpdateEventProtocolHandler` после commit. Обработчик вызывает `CleanupEventResultsService`; после очистки `Event` публикует `EventProtocolCleaned`, и `ParseEventProtocolHandler` запускает `ParseEventProtocolService`. При выключении этапа или ошибке обработки `DisableEventHandler` передаёт команду в тот же сервис очистки. Сервис удаляет производные строки и дистанции и пересчитывает затронутые разряды. Это отдельный жизненный цикл импортных данных; его нельзя автоматически трактовать как пользовательское удаление агрегата.

### Кубки, кэш и экспорт

`ViewCupTableService` и `ExportCupTableService` используют общий `CupTableBuilder` (`app/Application/Service/Cup/ViewCupTableService.php:24-66`, `app/Application/Service/Cup/ExportCupTableService.php:16-42`). `app/Infrastructure/Laravel/Cache/CachedCupTableBuilder.php:14-28` кэширует результат под тегом `cups`; `app/Bridge/Laravel/Cache/CacheManagerCupsCacheInvalidator.php:10-19` очищает тот же тег. `ClearCupCacheHandler` вызывается на событиях этапов, а обработчики событий очищают кэш после изменений связанных данных. Сервис очистки не требует ID кубка (`app/Application/Service/Cup/ClearCupCacheService.php`). При новом кэше определяйте ключ, теги и все источники инвалидации в одном контракте сценария.

### Проверки разрядов, аутентификация и интеграции

`RankCheckCreated` запускает `ProcessRankCheckService` после commit; сервис блокирует проверку и переводит её в Ready или Failed через методы агрегата (`app/Application/Handler/RankCheck/RankCheckCreatedHandler.php:10-20`, `app/Application/Service/RankCheck/ProcessRankCheckService.php:24-34`, `app/Domain/RankCheck/RankCheck.php:41-90`). V1 маршруты разделяют публичные, optional auth и authenticated запросы (`app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php:88-165`). Данные авторизованного пользователя выдаются через DTO/serializer, а не через Eloquent-модель напрямую (`app/Bridge/Laravel/Http/Controllers/ApiAction.php:80-102`).

Интеграция OrientBy находится в `app/Infrastructure/Integration/OrientBy`. `OrientBySyncService` сейчас напрямую меняет `Person` и вызывает `save()` (`app/Infrastructure/Integration/OrientBy/OrientBySyncService.php:61-116`); новый импорт должен вызывать Application-сценарии, чтобы сохранять правила агрегата. Парсеры старых форматов находятся также в `app/Models/Parser` и вызываются через legacy `app/Services/ParserService.php`.

## Правила жизненного цикла агрегата

1. Назовите границу агрегата и бизнес-переход. Не считайте любую Eloquent-строку отдельным агрегатом. `Distance`, `PersonRankHistory`, `RankCheckRow` и строки импорта требуют решения о владельце жизненного цикла в соответствующей фиче.
2. Создание задаёт `created` и `updated` с актором и временем, фиксирует событие создания и сохраняется через `Repository::add()`. Текущий паттерн вызывает aggregate `create()` из adapter.
3. Наблюдаемая бизнес-мутация происходит только через намеренный метод агрегата. Метод проверяет инвариант, обновляет `updated` через `Impression` и записывает событие о факте перехода. Application управляет блокировкой, транзакцией и сохранением через repository.
4. Отсутствие фактического изменения и идемпотентный повтор не должны создавать ложный новый бизнес-факт; условие no-op задаётся доменным правилом. Технический пересчёт или импорт без самостоятельного бизнес-смысла может не создавать событие, если спецификация явно описывает причину и способ аудита.
5. `Impression` (`app/Domain/Auth/Impression.php`) хранит `at` и `by`. Eloquent `updated_at` не заменяет `updated_by`. Если старой таблице не хватает полей, зафиксируйте переходное исключение и задачу миграции; не пишите несуществующий атрибут.
6. Публикуйте событие один раз после успешного сохранения. Обработчик, которому нужны записанные данные, ставьте после commit. Проверяйте повторную доставку, ошибки и побочные эффекты. Без надёжной доставки между БД и брокером строгую гарантию exactly-once обещать нельзя; требуемую надёжность определяет отдельный сценарий.
7. Бизнес-сущность с `active` выключается через `disable(Impression)`, событие и repository update. Обычные запросы скрывают отключённые записи; внутренние операции явно оговаривают доступ к ним. Для временных и производных данных допускается физическое удаление, если оно записано как политика типа.

### Матрица текущих агрегатов

| Тип | Бизнес-мутация и аудит | Событие | Удаление / статус | Вывод |
| --- | --- | --- | --- | --- |
| `Event` | `updateInfo`, `updateProtocol`, `disable` меняют `updated` (`app/Domain/Event/Event.php:75-98`) | Создание, обновления и выключение записывают события | `active=false`; repository фильтрует активные | Рабочий пример, но базовый Eloquent/event lifecycle переходный. |
| `Cup` | `updateData`, `disable` меняют `updated` (`app/Domain/Cup/Cup.php:48-65`) | Есть события | `active=false` | Рабочий пример. |
| `CupEvent` | `updateData`, `disable` меняют `updated` (`app/Domain/Cup/CupEvent/CupEvent.php:38-58`) | Есть события | `active=false` | Рабочий пример. |
| `Person` | `updateInfo`, `updateRanks`, `disable` меняют `updated` (`app/Domain/Person/Person.php:78-117`) | Есть события | `active=false` | Сама модель соответствует переходному шаблону; внешние прямые записи его обходят. |
| `PersonPayment` | `updateDate` меняет `updated` (`app/Domain/PersonPayment/PersonPayment.php:39-44`) | Есть события | Политика удаления отдельно не определена | Timestamps отключены, но `Impression` хранится. |
| `PersonPrompt` | `updateData`, `disable` меняют `updated` (`app/Domain/PersonPrompt/PersonPrompt.php:49-64`) | Есть события | `active=false` | Рабочий пример. |
| `Group` | `updateName` меняет `updated` (`app/Domain/Group/Group.php:48-53`) | Обновление без события; `disable` с событием | `active=false`; repository фильтрует активные | Добавить смысловой event изменения имени в отдельной фиче. |
| `Club` | `disable` меняет `updated` (`app/Domain/Club/Club.php:40-44`) | Выключение без события; `updateInfo` с событием | `active=false` | Уточнить downstream последствия и добавить событие выключения. |
| `Competition` | `updateInfo` меняет `updated` (`app/Domain/Competition/Competition.php:36-45`) | Обновление без события; `disable` с событием | `active=false` | Добавить событие обновления, если это наблюдаемый переход. |
| `ProtocolLine` | `assignPerson`, `setPerson`, `activateRank` принимают `Impression`, но не сохраняют `updated` (`app/Domain/ProtocolLine/ProtocolLine.php:105-127`) | Методы записывают события | Производные строки удаляются при замене протокола | Таблица без audit-полей; миграция нужна перед общим инвариантом. |
| `RankCheck` | `markReady`, `markFailed` меняют `updated` (`app/Domain/RankCheck/RankCheck.php:60-90`) | Есть события | Очистка физически удаляет временные проверки (`app/Infrastructure/Laravel/Eloquent/RankCheck/EloquentRankCheckRepository.php:50-52`) | Допустимое исключение, пока политика хранения явно ограничена. |

## Паттерны, которые стоит повторять

- Тонкий V1 action с command и Application service: `app/Bridge/Laravel/Http/Controllers/Api/V1/Competition/UpdateCompetitionAction.php` и `app/Application/Service/Competition/UpdateCompetitionInfoService.php:25-35`.
- Доменный метод с `Impression` и событием: `app/Domain/Event/Event.php:83-98`.
- Domain repository port и Eloquent adapter: `app/Domain/Event/EventRepository.php`, `app/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepository.php`.
- Разделение Criteria и Resources для читаемых запросов: `app/Domain/Event/EventResources.php`, `app/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepository.php:24-35`.
- Обработчик после commit, когда читает созданную запись: `app/Application/Handler/RankCheck/RankCheckCreatedHandler.php`.
- Общий builder для JSON и CSV и один cache decorator: `app/Application/Service/Cup/ViewCupTableService.php`, `ExportCupTableService.php`, `app/Infrastructure/Laravel/Cache/CachedCupTableBuilder.php`.

## Антипаттерны и реестр рефакторингов

Приоритет обозначает порядок отдельной будущей работы, а не срочность без проверки на production. Каждый пункт требует собственной спецификации и тестов при изменении поведения.

| ID | Приоритет | Наблюдение и риск | Ограниченный следующий шаг | Проверка и зависимость |
| --- | --- | --- | --- | --- |
| REF-01 | P1 | В фиче 025 буфер событий разделён на pending и history; повторная публикация устранена в коде. | Проверить регрессию на два save и один dispatch перед закрытием пункта. | Сохранить контракт `releasedEvents()` в тестах. |
| REF-02 | P1 | Очередные обработчики Person, Event и Cup переведены на `ShouldQueueAfterCommit`; гонка чтения незакоммиченной модели закрыта для этих обработчиков. События всё ещё сериализуют Eloquent-модели и могут видеть более позднее состояние; доставку при сбое после commit нужно оценить отдельно. | Передавать immutable факты или ID вместо моделей, проверить повторную доставку и необходимость outbox. | Интеграционный сценарий с ещё не завершённой транзакцией и тест повторной доставки; зависит от проверки REF-01. |
| REF-03 | P1 | `app/Application/Handler/Person/PersonDisabledHandler.php:13-19`, `app/Repositories/ProtocolLinesRepository.php:107-126` и `app/Services/ProtocolLineIdentService.php` напрямую меняют строки, обходя методы и события. | Вынести конкретные переходы в Application + Domain и ограниченный persistence adapter; отдельно решить bulk import. | Тесты идентификации/выключения проверяют связь, аудит, событие и отсутствие лишних запросов; зависит от политики `ProtocolLine` (REF-05). |
| REF-04 | P1 | `ParseEventProtocolService` сохраняет `failed` после ошибки, но сейчас ловит все `Throwable`; очередь может считать системный сбой обработанным. | Разделить ожидаемую ошибку формата и системный сбой, сохранив видимый статус и право очереди повторить временный сбой. | Интеграционные сценарии невалидного файла и временного сбоя. |
| REF-05 | P1 | `app/Domain/ProtocolLine/ProtocolLine.php:69,105-127` принимает `Impression`, но таблица `protocol_lines` не хранит `updated`. | Решить, является ли строка самостоятельным агрегатом; затем добавить аудит или перенести его к владельцу перехода, мигрировать схему без потери данных. | Миграционный и API/handler сценарии назначения/разряда; предварительное DDD-решение об ownership. |
| REF-06 | P2 | `app/Domain/Group/Group.php:48-53`, `app/Domain/Club/Club.php:40-44`, `app/Domain/Competition/Competition.php:36-45` меняют бизнес-состояние без события. | По одному bounded context добавить недостающий смысловой event и проверить подписчиков; исключить event только по явному решению. | Unit + integration проверки одного dispatch и эффекта обработчиков; зависит от REF-01. |
| REF-07 | P2 | `app/Domain/RankCheck/StandardRankCheckPersonMatcher.php:9-16` зависит от legacy сервиса; `app/Domain/Cup/CupType/AbstractCupType.php:18,29-33` зависит от legacy репозитория. | Ввести узкий доменный порт под нужный запрос или расширить существующий порт только при совпадении ответственности; adapter поместить в Infrastructure. | Архитектурная проверка направлений и регрессия расчёта кубка/матчинга; не наращивать общий repository без нужды. |
| REF-08 | P2 | `app/Services/ProtocolLineService.php:44-74,120-139` смешивает создание групп, дистанций, строк, идентификацию и прямые Eloquent-save; `ParseProtocolHandler` управляет тремя legacy сервисами. | Выделять по одному шагу импортного pipeline в Application/domain port и затем удалять освобождённые методы legacy сервиса. | Фикстуры парсеров, подсчёт строк и поведение повторного импорта; зависит от REF-04 и решения REF-05. |
| REF-09 | P2 | `app/Infrastructure/Integration/OrientBy/OrientBySyncService.php:61-138` напрямую меняет и сохраняет `Person`, местами создаёт её вне Application-сценария. | Перевести обработку одного человека на command/use case, а интеграцию оставить адаптером внешних данных. | Сценарии создания, обновления, платежа и повторного импорта; проверить события и аудит. |
| REF-10 | P2 | `app/Domain/Shared/AggregatedEvent.php:7-13` использует Laravel traits, события несут Eloquent-модели, например `app/Domain/Event/Event/EventInfoUpdated.php`; payload может отражать более позднее состояние. | Определить для каждого события immutable fact payload/ID и вынести framework dispatch за границу Domain. | Тест события после последующей мутации и обработка из очереди; зависит от решения о чистых агрегатах. |
| REF-11 | P3 | `app/Repositories/ProtocolLinesRepository.php:22-31` вручную создаёт Eloquent adapter и вызывает нестандартный `byCriteria(Criteria, array $with)` вне `ProtocolLineRepository`; `app/Domain/Shared/Criteria.php:52-55` возвращает объект исключения вместо `throw`. | Разделить cup query и общий repository, затем исправить контракт `Criteria::param()` отдельным узким багфиксом. | Unit-проверка отсутствующего параметра и query tests; не смешивать оба изменения в одном PR. |
| REF-12 | P3 | `app/Services/PersonsService.php`, `ParserService.php` и `ProtocolLineIdentService.php` остаются точками входа для CLI, импорта и парсинга. | После замены их потребителей удалить освобождённые legacy классы и `app/Repositories` только когда ссылок нет. | `rg` по ссылкам, узкие CLI/import tests; зависит от REF-07–REF-09. |
| REF-13 | P3 | `app/Domain/Shared/AggregatedModel.php`, `app/Domain/Auth/User.php`, `app/Domain/Distance/Distance.php` и другие Domain-модели привязаны к Eloquent. | Выбрать один агрегат с малым числом зависимостей, отделить чистую модель от Eloquent mapper и сравнить публичное поведение; переносить остальные по мере фич. | Unit Domain без Eloquent и integration adapter/API; зависит от стабилизации событий REF-01/REF-10. |

### Политика удаления по типам

`Event`, `Competition`, `Cup`, `CupEvent`, `Person`, `Group`, `Club`, `PersonPrompt` имеют `active` и должны использовать `disable()` для бизнес-удаления. Repository скрывает неактивные записи в обычных запросах; внутренний каскад не должен ошибочно требовать видимости через публичный API. `RankCheck` сейчас физически удаляется при очистке старых проверок; импортные `ProtocolLine` и `Distance` могут удаляться при замене протокола. Эти исключения относятся к временным/производным данным и требуют указанного retention policy в новой затрагивающей фиче. Для `PersonPayment` и прочих типов без `active` метод удаления не следует придумывать по аналогии; сначала определить жизненный цикл и владельца данных.

## Проверочный список для AI-агента и ревьюера

### История обновлений spec-kit

Каждая завершённая фича из `specs/` должна получить ровно одну запись на авторизованной странице `/app/updates` в `resources/spa/pages/updates/updates.ts`. До закрытия `tasks.md` добавьте дату фиксации изменения, короткое пользовательское описание и подробности; пример и ссылку на актуальный раздел давайте, когда они помогают воспользоваться функцией. Если новая задача заменяет старую функцию, обновите прежнюю запись и объясните замену текстом без ссылки на GitHub. Проверьте порядок записей и отсутствие ссылок на удалённые маршруты. Даты из истории Git называйте датами фиксации, если дата выката неизвестна. Это требование действует и для внутренних изменений без отдельной страницы: им нужна запись с понятным эффектом, но выдумывать пользовательскую ссылку не следует.

Перед планом новой бэкенд-фичи:

- Указаны агрегат и граница его инварианта; новая бизнес-логика не помещена в `app/Services`, `app/Repositories`, action или Eloquent adapter.
- Определены command, валидируемый DTO, Application service, domain method, repository port/adapter и assembler, если они нужны сценарию.
- Для чтения заданы Criteria, Resources, ограничения выборки и API-контракт; нет скрытых N+1 и передачи Eloquent через HTTP.
- Для записи указаны transaction/lock, `created`/`updated` и актор, событие, момент публикации и after-commit политика обработчика.
- Названы no-op, повторная доставка, частичный сбой и способ увидеть ошибку очереди; кэш имеет ключ и все пути инвалидации.
- Удаление явно классифицировано как бизнес-выключение либо очистка временных/производных данных; определена видимость выключенных объектов.
- Переходное исключение документировано в `spec.md` вместе с ограниченной задачей устранения.
- Для завершённой spec-kit фичи добавлена запись в `resources/spa/pages/updates/updates.ts`; при замене прежней функции обновлено её описание без ссылки на GitHub.

При PR review сверяйте эти пункты с исходными файлами и request/integration/unit тестами изменённого поведения. Для V1 API дополнительно применяйте раздел VIII конституции и `@see`-связь action с request-тестом. Для документационного PR проверяйте ссылки и отсутствие изменений поведения. При расхождении между этим файлом и конституцией исправляйте манифест или оформляйте отдельную поправку к конституции.
