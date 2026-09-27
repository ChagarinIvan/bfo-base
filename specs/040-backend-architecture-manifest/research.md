# Исследование архитектуры бэкенда

**Дата снимка**: 2026-09-27. Основание: текущая рабочая копия, без запуска приложения и тестов.

## Решение 1. Канонический документ

**Decision**: Хранить `.specify/memory/backend-architecture-manifest.md` рядом с конституцией. Конституция задаёт обязательные правила, манифест показывает фактический путь, исключения и реестр долга. `AGENTS.md` и `CLAUDE.md` требуют читать оба документа для новых бэкенд-фич и ревью.

**Rationale**: Эти два файла уже являются входом для агентов; существующая конституция запрещает Eloquent в Domain, хотя текущий `app/Domain/Shared/AggregatedModel.php` наследует `Illuminate\Database\Eloquent\Model`. Нужны одновременно неизменная цель и ясная переходная карта.

**Alternatives considered**: Отдельная спецификация без обязательной ссылки не участвовала бы в следующих фичах; копирование полного руководства в оба файла инструкций создало бы расхождения.

## Решение 2. Мутация и аудит

**Decision**: Наблюдаемый бизнес-переход выполняет намеренный метод агрегата, обновляет `Impression updated`, записывает доменное событие и сохраняется через repository в транзакции. Создание заполняет `created` и `updated`. Технические переписывания проекций и импортные строки требуют отдельной классификации.

**Rationale**: `Event::updateInfo()` (`app/Domain/Event/Event.php:83-90`) и `UpdateEventService` (`app/Application/Service/Event/UpdateEventService.php:36-58`) уже показывают этот путь. `Group::updateName()` (`app/Domain/Group/Group.php:48-53`), `Competition::updateInfo()` (`app/Domain/Competition/Competition.php:36-45`) и `Club::disable()` (`app/Domain/Club/Club.php:40-44`) не записывают события. `ProtocolLine` выключает timestamps и не имеет поля `updated` (`app/Domain/ProtocolLine/ProtocolLine.php:69,105-127`; `database/migrations/2020_10_29_191124_create_protocol_lines_table.php:18-30`).

**Alternatives considered**: Требовать событие для каждого SQL `UPDATE` породило бы ложные бизнес-факты при пересчётах и импорте. Разрешить все технические исключения неявно позволило бы обойти инвариант агрегата.

## Решение 3. Публикация и доставка событий

**Decision**: Зафиксировать отдельные стадии record, persist, publish, commit, consume. Новые обработчики, читающие результат записи, запускаются после commit. Для повторных доставок обработчики проектируются идемпотентными. Буфер pending событий очищается после успешной публикации без потери возможности тестировать историю.

**Rationale**: `AggregatedModel::save()` публикует события после `parent::save()`, но `releaseEvents()` не очищает `$modelEvents` (`app/Domain/Shared/AggregatedModel.php:15-37`), поэтому повторный save может повторить старые события. `EventHandlerServiceProvider` включает автопоиск обработчиков (`app/Bridge/Laravel/Provider/EventHandlerServiceProvider.php:11-26`). `RankCheckCreatedHandler` использует `ShouldQueueAfterCommit` (`app/Application/Handler/RankCheck/RankCheckCreatedHandler.php:10-20`), а `UpdateEventInfoHandler` использует `ShouldQueue` (`app/Application/Handler/Event/UpdateEventInfoHandler.php:9-20`). Очередь Redis задана по умолчанию (`config/queue.php:17,62-68`). `releasedEvents()` используется тестами как исторический список (`tests/Domain/Event/EventUpdateTest.php:32,52`), поэтому простая очистка одного массива разрушит текущий тестовый контракт.

**Alternatives considered**: Поменять все обработчики без исследования зависимостей было бы слишком широким рефакторингом; оставить публикацию до commit не даёт гарантию, что worker увидит сохранённые данные.

## Решение 4. Удаление и видимость

**Decision**: Для бизнес-сущностей с `active` использовать `disable()` с `updated` и событием; query-порты по умолчанию исключают отключённые данные. Физическое удаление временных/производных записей явно документируется по типу.

**Rationale**: `Event::disable()` (`app/Domain/Event/Event.php:75-80`) и фильтр `EloquentEventRepository` (`app/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepository.php:24-42,110-113`) дают пример. `RankCheck` удаляется физически по политике очистки (`app/Infrastructure/Laravel/Eloquent/RankCheck/EloquentRankCheckRepository.php:50-52`); импортированные строки протокола удаляются при замене события (`app/Services/ProtocolLineService.php:93-96`). Эти случаи нельзя без анализа приравнять к удалению бизнес-сущности.

**Alternatives considered**: Ввести Laravel SoftDeletes для всех таблиц означало бы миграции и изменение запросов без обоснованной бизнес-политики.

## Решение 5. Последовательность дальнейшего рефакторинга

**Decision**: Отдельные будущие фичи начинаются с жизненного цикла событий и прямых записей в обход агрегатов; затем разбирают импорт протоколов, зависимости Domain от legacy и отделение Eloquent-моделей от домена.

**Rationale**: `PersonDisabledHandler` напрямую меняет `ProtocolLine` (`app/Application/Handler/Person/PersonDisabledHandler.php:13-19`); `app/Repositories/ProtocolLinesRepository.php:107-126` делает bulk update `person_id`; `app/Domain/RankCheck/StandardRankCheckPersonMatcher.php:9-16` зависит от legacy сервиса; `app/Domain/Cup/CupType/AbstractCupType.php:18,29-33` зависит от legacy репозитория. `ParseProtocolHandler` ловит и только логирует все `Exception` (`app/Application/Handler/Event/ParseProtocolHandler.php:29-48`), поэтому задача очереди может завершиться успешно при ошибке парсинга.

**Alternatives considered**: Одномоментный перенос всех Eloquent-агрегатов затронул бы почти все предметные области и смешал архитектурную миграцию с исправлением реальных ошибок.
