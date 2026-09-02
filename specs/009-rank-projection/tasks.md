---

description: "Задачи реализации актуального разряда и истории разрядов"
---

# Задачи: актуальный разряд и история разрядов

**Входные документы**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [контракты](./contracts/) и [quickstart.md](./quickstart.md)

**Тесты**: обязательны по FR-011. Domain/Application unit-тесты используют только value objects, стабы и моки; Eloquent и БД допустимы лишь в Infrastructure/HTTP integration-тестах. Полные `composer cs`, `composer stan`, `composer rector -- --dry-run` и `composer test` запускаются один раз в финальной фазе.

**Организация**: задачи сгруппированы по пользовательским историям. `Rank` означает новый integer-backed enum; legacy Eloquent aggregate с тем же именем удаляется в рамках миграции. `PersonRankHistory` хранится как обычная Eloquent-модель без `AggregatedModel` и сохраняется репозиторием через `save()`.

## Фаза 1: Подготовка и фиксация поведения

**Цель**: описать заменяемый путь и защитить спортивные правила от архитектурного рефакторинга.

- [X] T001 Составить исходную инвентаризацию legacy-точек разрядов и их замен в `specs/009-rank-projection/legacy-inventory.md` по usages `app/Services/RankService.php`, `app/Repositories/RanksRepository.php`, `app/Domain/Rank/Rank.php`, actions, commands, jobs, providers, routes, Blade, переводам и тестам.
- [X] T002 [P] Перенести все наблюдаемые ветви текущего расчёта в чистые характеризационные тесты без Eloquent в `tests/Domain/Rank/RankCalculationCharacterizationTest.php`: отсутствие выполнения, автоматическая и ручная активация, продление, повышение, понижение, возраст и IIIю.
- [X] T003 [P] Зафиксировать интеграционными регрессионными сценариями поведение `protocol_lines.activate_rank`, отвязки/перепривязки строки и полного refill в `tests/Feature/Rank/RebuildRanksAfterProtocolProcessingTest.php`, `tests/Bridge/Laravel/Http/Controllers/Rank/UpdateRankActivationDateActionTest.php` и `tests/Feature/Rank/RefillPersonRanksTest.php`.
- [X] T004 [P] Зафиксировать, что `OrientBy` не создаёт и не меняет разряд без protocol line, в `tests/Infrastructure/Integration/OrientBy/OrientBySyncServiceTest.php`.

**Контрольная точка**: все действующие спортивные правила имеют воспроизводимые тесты до удаления legacy-кода.

---

## Фаза 2: Общая основа (блокирует пользовательские истории)

**Цель**: создать чистую доменную модель, схему проекции и целевые persistence-порты без параллельного legacy-пути.

- [X] T005 Атомарно переключить usages с прежнего Eloquent aggregate/repository на целевую модель, удалив legacy-контракт из `app/Domain/Rank/RankRepository.php` и заменив прежний `app/Domain/Rank/Rank.php` новым enum без промежуточного рабочего состояния с двумя типами `Rank`.
- [X] T006 Реализовать integer-backed enum, нормализацию обозначений из protocol line, порядок силы, labels и признаки спортивных правил в `app/Domain/Rank/Rank.php` и покрыть cases/invalid input в `tests/Domain/Rank/RankTest.php`.
- [X] T007 [P] Ввести чистые входные и выходные объекты расчёта в `app/Domain/Rank/RankFact.php`, `app/Domain/Person/PersonRank.php`, `app/Domain/Rank/PersonRankState.php` и `app/Domain/Rank/PersonRankHistory.php`.
- [X] T008 Реализовать чистый калькулятор, перенеся в него подтверждённое T002 поведение, в `app/Domain/Rank/RankCalculator.php` и адаптировать `tests/Domain/Rank/RankCalculationCharacterizationTest.php` к новому входу.
- [X] T009 Расширить aggregate `Person` операцией атомарной замены materialized rank state и owned history в `app/Domain/Person/Person.php`, не загружая историю при обычном чтении человека.
- [X] T010 Добавить обратимую миграцию `database/migrations/*_add_current_rank_projection_to_persons.php` для `persons.current_rank`, дат актуального периода и lean-индексов `(current_rank, active)` и `current_rank_finished_on`.
- [X] T011 Добавить обратимую миграцию `database/migrations/*_create_person_rank_histories_table.php` с protocol line, прямыми `distance_id`/`event_id`/`competition_id`, датами, типом изменения и индексами из `specs/009-rank-projection/data-model.md`.
- [X] T012 Выделить чтение фактов protocol lines в целевой порт `app/Domain/Rank/RankFactsCollector.php` и Eloquent adapter `app/Infrastructure/Laravel/Eloquent/Rank/EloquentRankFactsCollector.php`; это заменяет legacy repository без создания нового `ProtocolLineRepository`-контракта.
- [X] T013 Изменить `app/Infrastructure/Laravel/Eloquent/Person/EloquentPersonRepository.php` и `app/Domain/Person/PersonRepository.php`, чтобы они транзакционно сохраняли текущие поля Person и заменяли принадлежащую историю без отдельного rank repository.
- [X] T014 Покрыть persistence-схему, enum casts, атомарную замену истории и требуемые планы запросов в `tests/Infrastructure/Laravel/Eloquent/Person/EloquentPersonRepositoryTest.php` и `tests/Infrastructure/Laravel/Eloquent/Person/PersonRankQueryPlanTest.php`; подготовить в `app/Bridge/Laravel/Console/Commands/RefillPersonRanksCommand.php` безопасный первичный полный backfill после миграции.

**Контрольная точка**: существует единственная чистая модель разряда, а schema/persistence готовы для rebuild без legacy Rank.

---

## Фаза 3: Пользовательская история 1 — просмотр и фильтрация актуального разряда (P1) 🎯 MVP

**Цель**: список спортсменов читает сохранённый `rankId`, быстро фильтрует по нему и клиент получает справочник для отображения.

**Независимая проверка**: создать в БД спортсменов с разным сохранённым состоянием, запросить `/api/v1/persons?rankId=…` и `/api/v1/ranks`; список не выполняет расчёта или запросов на каждый элемент.

### Тесты пользовательской истории 1

- [X] T015 [P] [US1] Добавить API integration-тесты `rankId`, фильтра `0` (без разряда), ошибок недопустимого ID, пагинации и отсутствия N+1 в `tests/Feature/Api/V1/Person/ListPersonsActionTest.php`.
- [X] T016 [P] [US1] Добавить contract-тест стабильного порядка и `id`/`label` справочника в `tests/Feature/Api/V1/Rank/ListRanksActionTest.php`.
- [X] T017 [P] [US1] Добавить unit-тест кэша справочника (memory, один час `localStorage`, invalid cache) в `resources/spa/api/ranks.test.ts`.

### Реализация пользовательской истории 1

- [X] T018 [US1] Расширить command/criteria и DTO списка в `app/Application/Service/Person/ListPersons.php`, `app/Application/Dto/Person/SearchPersonDto.php` и `app/Application/Dto/Person/ViewPersonDto.php` полем enum-backed `rankId` без вычисления истории.
- [X] T019 [US1] Реализовать фильтрацию materialized `current_rank` и оптимизированное чтение списка в `app/Infrastructure/Laravel/Eloquent/Person/EloquentPersonRepository.php`.
- [X] T020 [US1] Передать `rankId` через `app/Bridge/Laravel/Http/Controllers/Api/V1/Person/ListPersonsAction.php` и `app/Bridge/Laravel/Http/Resources/PersonResource.php`, удалив service locator/read-time `ActivePersonRankService` из этого пути.
- [X] T021 [US1] Создать application read use case и action справочника в `app/Application/Service/Rank/ListRanks.php` и `app/Bridge/Laravel/Http/Controllers/Api/V1/Rank/ListRanksAction.php`, затем зарегистрировать `GET /api/v1/ranks` в актуальном V1 route-регистраторе.
- [X] T022 [US1] Реализовать типизированный API-клиент и кэш справочника в `resources/spa/api/ranks.ts`, не дублируя enum values или labels на фронтенде.
- [X] T023 [US1] Выполнить узкие тесты T015–T017 и проверить `EXPLAIN` rank/filter queries из `tests/Infrastructure/Laravel/Eloquent/Person/PersonRankQueryPlanTest.php`.

**Контрольная точка**: API списка и справочника независимо пригодны для текущих и будущих экранов спортсменов.

---

## Фаза 4: Пользовательская история 2 — автоматическое обновление после протокола (P1)

**Цель**: обработка протокола, идентификация и ручная активация обновляют только затронутых уникальных спортсменов через один rebuild path.

**Независимая проверка**: обработать протокол с несколькими строками одного спортсмена и убедиться, что его история и current state пересчитаны один раз; изменить `activate_rank`, привязку и отвязку строки и получить согласованный результат.

### Тесты пользовательской истории 2

- [X] T024 [P] [US2] Добавить mock-based unit-тесты дедупликации person IDs, payload фактов и транзакционного сохранения в `tests/Application/Service/Rank/RebuildPersonRanksServiceTest.php`.
- [X] T025 [P] [US2] Добавить integration-тесты перепривязки/отвязки protocol line и одного queued rebuild на уникального спортсмена в `tests/Feature/Rank/RebuildRanksAfterProtocolProcessingTest.php` и `tests/Feature/Rank/ProtocolLineRankRebuildDispatchTest.php`.
- [X] T026 [P] [US2] Добавить integration-тест изменения `protocol_lines.activate_rank` и сохранения даты после rebuild в `tests/Bridge/Laravel/Http/Controllers/Rank/UpdateRankActivationDateActionTest.php`.

### Реализация пользовательской истории 2

- [X] T027 [US2] Реализовать Application use case batch rebuild с блокировкой Person, чистым `RankCalculator` и транзакционным `PersonRepository` в `app/Application/Service/Rank/RebuildPersonRanksService.php` и `app/Application/Service/Rank/RebuildPersonRanks.php`.
- [X] T028 [US2] Переключить orchestration идентификации на единственную идемпотентную batch-задачу `RebuildPersonRanksJob`: `ProtocolLineIdentService` собирает unique person IDs и передаёт их T027; отдельный rebuild на каждую строку удалён.
- [X] T029 [US2] Перевести изменение даты ручной активации на `ProtocolLine.activateRank()` и rebuild владельца строки в `app/Application/Service/Person/UpdatePersonRankActivationDateService.php`, `app/Application/Service/Person/ActivatePersonRankService.php` и соответствующих HTTP actions.
- [X] T030 [US2] Добавить queued batch handler для rebuild в `app/Bridge/Laravel/Jobs/RebuildPersonRanksJob.php`; job дедуплицирует person IDs, а сервис разрешается стандартным DI и идемпотентен.
- [X] T031 [US2] Выполнить узкие тесты T024–T026 в `tests/Application/Service/Rank/RebuildPersonRanksServiceTest.php`, `tests/Feature/Rank/RebuildRanksAfterProtocolProcessingTest.php` и `tests/Feature/Rank/ProtocolLineRankRebuildDispatchTest.php`; последний подтверждает одну job на уникальный person ID.

**Контрольная точка**: никакая идентифицированная protocol line не зависит от read-time расчёта; изменения источника запускают единственный batch rebuild.

---

## Фаза 5: Пользовательская история 3 — прозрачная история и повторный пересчёт (P2)

**Цель**: сотрудник видит объяснимую историю с прямыми ссылками, а refill любого масштаба воспроизводит её только из protocol lines.

**Независимая проверка**: выполнить refill дважды для последовательности выполнений, продлений и понижений; результаты и current state идентичны, а Blade-история показывает источник и даты без N+1.

### Тесты пользовательской истории 3

- [X] T032 [P] [US3] Добавить тест единственной сохранённой команды полного refill в `tests/Bridge/Laravel/Console/Commands/RefillPersonRanksCommandTest.php`; отдельная Application-обёртка и web refill entry point удалены.
- [X] T033 [P] [US3] Добавить integration-тесты идемпотентного refill, исключения non-protocol данных и прямых source IDs в `tests/Feature/Rank/RefillPersonRanksTest.php`.
- [X] T034 [P] [US3] Добавить HTTP integration-тест отображения истории с batch-loaded competition/event/distance в `tests/Bridge/Laravel/Http/Controllers/Rank/ShowPersonRanksActionTest.php`.

### Реализация пользовательской истории 3

- [X] T035 [US3] Реализовать чтение истории через `ViewPerson` с опциональным `withRanksHistory`, eager-load relation в `ViewPersonService`, сборку в `PersonAssembler` и `PersonRankHistoryDto`, без загрузки истории на обычном списке персон.
- [X] T036 [US3] Перевести history action и Artisan entry point на T035/T027; отдельный веб-refill action и его оболочка удалены как лишняя точка входа, полный refill выполняется через `persons:ranks:refill {userId}`.
- [X] T037 [US3] Подключить ежедневный Scheduler, который находит только истёкшие `current_rank_finished_on` и отправляет их в T027, в `app/Bridge/Laravel/Console/Kernel.php` и `app/Application/Service/Rank/RebuildExpiredPersonRanksService.php`.
- [X] T038 [US3] Выполнить узкие тесты full refill, истории и ежедневного истечения в `tests/Bridge/Laravel/Console/Commands/RefillPersonRanksCommandTest.php`, `tests/Feature/Rank/RefillPersonRanksTest.php`, `tests/Feature/Rank/RebuildExpiredPersonRanksTest.php` и `tests/Bridge/Laravel/Http/Controllers/Rank/ShowPersonRanksActionTest.php`.

**Контрольная точка**: история объясняет каждый переход, может читаться без вложенного N+1 и полностью восстанавливается из protocol lines.

---

## Фаза 6: Пользовательская история 4 — завершение миграции без мёртвого legacy (P3)

**Цель**: в приложении остаётся один целевой путь разрядов; удалённые точки не доступны через route, binding, command или UI.

**Независимая проверка**: финальная инвентаризация не содержит рабочих ссылок на старые rank services/repositories; сохранённые Blade-сценарии используют T035/T027.

- [X] T039 [US4] Обновить `specs/009-rank-projection/legacy-inventory.md` фактическим решением для каждого найденного элемента и проверить usages после переключения.
- [X] T040 [US4] Удалить заменённые legacy rank services, repositories, factories, providers, filters, events и bindings из `app/Services/RankService.php`, `app/Repositories/RanksRepository.php`, `app/Infrastructure/Laravel/Eloquent/Rank/`, `app/Application/Service/Rank/ActivePersonRankService.php` и связанных файлов из T039.
- [X] T041 [US4] Удалить заменённые HTTP actions, console commands, routes, Blade links, translations и их тесты из `app/Bridge/Laravel/Http/Controllers/Rank/`, `app/Bridge/Laravel/Console/Commands/`, `resources/views/`, `lang/` и `tests/`, сохранив только страницы истории, переведённые на T035.
- [X] T042 [US4] Проверить `app/Infrastructure/Integration/OrientBy/OrientBySyncService.php`: импорт не создаёт legacy `Rank` и не сохраняет внешний rank без protocol line; синхронизация работает только с Person/Club/Payment данными.
- [X] T043 [US4] Проверить `rg`-поиск и route list для удалённых входов; добавить регрессионные проверки отсутствия legacy behaviours в `tests/Feature/Rank/LegacyRankRemovalTest.php`.

**Контрольная точка**: нет параллельного расчёта или persistence разрядов, а каждое сохранённое пользовательское действие использует новый путь.

---

## Фаза 7: Финальная проверка и документация

**Цель**: подтвердить соответствие спецификации, производительность и Definition of Done один раз после завершения всех фаз.

- [X] T044 Обновить результаты quickstart и финальный статус legacy-инвентаризации в `specs/009-rank-projection/quickstart.md` и `specs/009-rank-projection/legacy-inventory.md`.
- [X] T045 Выполнить полный набор гейтов один раз: `composer cs`, `composer stan`, `composer rector -- --dry-run`, `composer test` и frontend CI `npm run ci`; зафиксировать результат в `specs/009-rank-projection/quickstart.md`.
- [X] T046 Выполнить API/SPA validation из `specs/009-rank-projection/quickstart.md`, проверить N+1 и `EXPLAIN` трёх запросов, затем зафиксировать результаты в `specs/009-rank-projection/quickstart.md`.
- [X] T047 Сверить итоговую реализацию с FR-001–FR-020, SC-001–SC-008 и контрактами в `specs/009-rank-projection/spec.md` и `specs/009-rank-projection/contracts/`.

### Правки по PR review

- [X] T048 Исправить удаление/замену протокола: до cleanup собрать unique person IDs, выполнить rebuild с `Impression` события и удалить derived history каскадно вместе с источником.
- [X] T049 Восстановить продление периода для активных одинаковых разрядов; отделить `RankNormalizer`, оставить Carbon в domain calculation и ввести `PersonRank` + `PersonRanksUpdated`.
- [X] T050 Убрать выделенный persistence/read path projection: use case меняет aggregate и вызывает `PersonRepository::update()`, history читается сервисами через Person; удалить Application ports и adapters.
- [X] T051 Сделать refill/expiry потоковыми; команды принимают `userId`, выводят start/finish, а сервисы создают `Impression` через `Clock`; event/job paths передают автора исходного события.
- [X] T052 Перевести `Rank` и контракт SPA/API на integer backed values: хранить `current_rank` и history `rank` в `unsignedTinyInteger`, передавать числовой `rankId` и сравнивать enum по `value` без `strength()`.

---

## Зависимости и порядок выполнения

```text
Фаза 1 → Фаза 2
Фаза 2 → US1 (фаза 3)
Фаза 2 → US2 (фаза 4)
US2 → US3 (фаза 5)
US1 + US2 + US3 → US4 (фаза 6)
US1 + US2 + US3 + US4 → фаза 7
```

- **US1 (P1)** после фазы 2 независима как API-путь: её можно проверить на сохранённой проекции, даже до подключения автоматического rebuild.
- **US2 (P1)** после фазы 2 строит и поддерживает эту проекцию; T027 является единственным writer path.
- **US3 (P2)** зависит от T027, поскольку refill и история используют ту же модель и rebuild.
- **US4 (P3)** начинается только после переключения всех читателей и writers, чтобы не удалить требуемую совместимость.

## Возможности параллельной работы

- В фазе 1 параллельны T002, T003 и T004 после начала T001.
- В фазе 2 T007, T010 и T011 могут выполняться параллельно после решения T005–T006; T012 и T013 — после определения доменных портов.
- В US1 тесты T015–T017 независимы; в US2 независимы T024–T026; в US3 независимы T032–T034.
- Разные файлы API-клиента (T022) и backend read-пути (T018–T021) можно выполнять параллельно после согласования контракта.

## Стратегия реализации

### MVP

Минимально полезный вертикальный срез — фазы 1–3: материализованное состояние, `GET /api/v1/ranks` и фильтр `/api/v1/persons?rankId=…`. Он снимает read-time нагрузку и делает фильтрацию доступной, но до фазы 4 требует контролируемого первичного refill для наполнения данных.

### Инкременты

1. Фазы 1–2 — неизменные правила и чистая основа.
2. US1 — быстрое чтение и фильтр.
3. US2 — автоматическая достоверность проекции после протокола.
4. US3 — объяснимая история, refill и плановое истечение.
5. US4 и фаза 7 — удаление второй системы и финальные гейты.

Каждая контрольная точка допускает только узкие тесты изменённого поведения. Полные проверки запускаются только в T045.

## Phase 8: Convergence

Ретроспективная сверка реализации со спецификацией 2026-09-02 выявила один остаточный
оптимизационный разрыв в сценарии замены протокола:

- [ ] T053 Объединить старые `person_id`, собранные до cleanup, с новыми ID после идентификации и отправлять одну batch-задачу rebuild после завершения замены протокола по FR-003/FR-004 (partial).
