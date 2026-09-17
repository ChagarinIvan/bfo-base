---
name: api-application-patterns
description: Проектные паттерны для простых API actions и Application-сервисов в BFO Base.
---

# Простые API и Application-сервисы BFO Base

Используй этот skill перед созданием или рефакторингом нового API-сценария. Его цель —
сначала найти существующий проектный паттерн, а не проектировать новый мини-фреймворк.

## Обязательная разведка

Перед изменениями открой минимум два близких примера:

- `app/Bridge/Laravel/Http/Controllers/Api/V1/Event/CreateEventAction.php` и
  `app/Application/Service/Event/AddEventService.php` — создание ресурса;
- `app/Bridge/Laravel/Http/Controllers/Api/V1/Event/ListEventsAction.php` и
  `app/Application/Service/Event/ListEventsService.php` — paginated list;
- соответствующий `*Assembler`, `*Dto`, repository interface и Eloquent adapter.

Проверь поиск по проекту, прежде чем вводить новый класс:

```sh
rg --files app/Bridge/Laravel/Http/Controllers/Api/V1 app/Application/Service
rg "class .*Action|class .*Service|interface .*Repository" app
```

## Правила слоя Bridge/API

Action — тонкий адаптер транспорта:

- принимает уже валидируемые `AbstractDto`, стандартные value objects и Application-сервис;
- создаёт command и вызывает `$service->execute($command)`;
- возвращает DTO или `Slice`; сериализация, HTTP status, ошибки и pagination headers
  выполняются `ApiAction` и существующими атрибутами;
- не содержит `Request`, ручную валидацию, `JsonResponse`, assembler-логику или SQL.

Для multipart используй DTO с `requestValidationRules()` по образцу существующих file DTO.
Не проверяй файл повторно в action и не дублируй стандартную `422`-обработку.

## Правила Application

- Command хранит вход сценария и предоставляет методы, возвращающие domain input/value objects;
  не передавай HTTP Request или сырые transport-структуры глубоко в домен.
- Service координирует factory, repository, transaction и assembler. Он не должен содержать
  правила переходов aggregate, нормализацию доменных значений или SQL.
- Domain factory создаёт сущность и сообщает доменные ограничения через domain exception;
  Application service проксирует её в Application exception с `#[HttpError]`, если это нужно API.
- Результат формируй через существующий assembler. Для `created`/`updated` используй
  `AuthAssembler::toImpressionDto()`, не изобретай собственные timestamp payloads.
- Для списка возвращай `Slice<Dto>`; action применяет стандартные page/perPage, а `ApiAction`
  добавляет pagination headers. Не создавай ручной объект `pagination` внутри DTO без примера.

## Domain, события и persistence

- Новые порты размещай в Domain/Application согласно ближайшим примерам; Eloquent и Laravel
  детали остаются в Infrastructure/Bridge.
- Репозитории принимают доменные сущности (`add(Entity)`, `update(Entity)`, `delete(Entity)`),
  а не `create(userId, ...)` и не сырые массивы.
- Aggregate владеет переходами статусов и бросает domain exceptions. `save()` выполняет
  repository; исключение — фабричный `create()`, когда это уже установленный паттерн проекта.
- Если создание публикует domain event, обработчик события запускает очередь. Не dispatch-ь
  job вручную из create service после `add()`.
- Долгую обработку не жди в HTTP-запросе: создай pending aggregate, верни DTO, а worker
  обновляет aggregate в transaction и меняет `updated` через Impression.

## Тесты и проверка

- Application/Domain unit tests мокируют repositories и collaborators; Eloquent записи создаются
  только в integration/API request tests.
- Проверяй payload и вызовы: factory, repository add/update, event/queue boundary, assembler,
  отсутствие ожидания worker и переходы статуса.
- Для paginated read проверяй criteria и mapping в `Slice`, а не ручную сборку headers.
- После изменения сценария запускай узкие тесты, затем в конце `composer test`, `composer stan`,
  `composer cs`, Rector dry-run, frontend typecheck/tests/build и `git diff --check`.

## Антипаттерны

Не добавляй ручные `JsonResponse` в обычный action, `Request` в Application service, SQL или
Eloquent query в Application, новые legacy-сервисы в `app/Services`, repository `create()` с
примитивами, повторную валидацию DTO, embedded rows/pagination если проект уже возвращает
отдельный `Slice`, либо прямой `dispatch()` для работы, которую должен начать domain event.
