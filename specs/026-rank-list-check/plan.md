# Implementation Plan: Асинхронная проверка разрядов по списку

**Branch**: `026-rank-list-check` | **Date**: 2026-09-16 | **Spec**: [spec.md](spec.md)

## Summary

Добавить read-only запуск `RankCheck`, доступный только аутентифицированным API-пользователям.
API принимает CSV и сразу возвращает `202` с ID и `PARSING`; обработчик доменного события `RankCheckCreated`, выполняемый через очередь, запускает исторический
pipeline парсинга/идентификации/получения разряда через domain processor, сохраняет снимок строк и переводит
запуск в `READY` или `FAILED`. SPA предоставляет навигационный пункт, форму, polling и
семиколоночный результат старой проверки с серверной пагинацией через отдельный rows endpoint.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript/Vue 3

**Primary Dependencies**: Laravel 13, Redis queue/Horizon, Eloquent, existing parser and person/rank services behind feature ports

**Storage**: MySQL for `RankCheck`/`RankCheckRow`; existing private file storage for source file; Redis for queue; daily scheduler for retention

**Testing**: PHPUnit request/application/domain tests; Vitest SPA tests; project CS, PHPStan, Rector and frontend gates

**Target Platform**: Existing Laravel API and SPA deployment

**Project Type**: Web application with API and SPA

**Performance Goals**: Create request returns within 2 seconds independent of list processing time; status visible within 5 seconds of final transition

**Constraints**: No synchronous full-list processing; no mutation of person/rank data; no partial rows exposed; bounded polling and authentication; result pages must be server-paginated

**Scale/Scope**: One independent run per upload; arbitrary supported list size within configured upload/storage/queue limits; v1 has no export or notifications

## Constitution Check

- Target layers: Domain entity/status/repository port; Application commands/services; Bridge actions/job/routes; Infrastructure Eloquent/file adapters.
- No new legacy `app/Services` or `app/Repositories` extension; existing parser/identification behavior is wrapped or injected through ports where needed.
- Application/Domain unit tests use mocks, not Eloquent entities; persistence and HTTP contracts use integration/request tests.
- Queue state transitions are explicit and transactional; errors are observable; source and personal data are excluded from ordinary logs.
- No new dependency is required.

## Project Structure

```text
app/Domain/RankCheck/
app/Application/Service/RankCheck/
app/Application/Port/RankCheck/
app/Bridge/Laravel/Http/Controllers/Api/V1/RankCheck/
app/Bridge/Laravel/Jobs/
app/Bridge/Laravel/Console/Commands/
app/Infrastructure/Laravel/Eloquent/RankCheck/
app/Infrastructure/RankCheck/
database/migrations/
resources/spa/api/rankChecks.ts
resources/spa/pages/rank-checks/
tests/Domain/RankCheck/
tests/Application/Service/RankCheck/
tests/Feature/Api/V1/RankCheck/
resources/spa/**/*.test.ts
```

**Structure Decision**: Follow the existing target-layer architecture. Application services
coordinate repositories, shared `Storage`, factories, transactions and assemblers. The preserved
parser is wrapped by `StandardRankListParser`, which returns normalized `RankListItem` DTOs. The
domain `StandardRankCheckProcessor` performs matching, snapshotting and row creation; its failures
are represented by `ProcessError`, handled by `RankCheck::process()`, and persisted by the service.
Person prompt lookup and the legacy fallback are encapsulated by `StandardRankCheckPersonMatcher`.
Eloquent, file storage, Laravel queue and scheduler details remain in Infrastructure/Bridge.
Status and rows are separate reads: `ViewRankCheckDto` uses the existing `AuthAssembler` for
`created`/`updated`, while the rows action returns the standard paginated `Slice`. Bindings are
isolated in `RankCheckProvider`, and generic file `Storage` is bound in `SharedProvider`.

## Complexity Tracking

No constitution violations identified.

## Исправление production timeout, 2026-10-03

`RankCheckCreatedHandler` выполняется в очереди `rank-checks` через отдельное подключение
`redis-rank-checks` к тому же Redis. Лимит задачи составляет 300 секунд, Horizon supervisor
имеет лимит 330 секунд, а `retry_after` подключения равен 360 секундам. Один воркер обслуживает
эту очередь в production и local. Пять минут являются ограничением попытки, а не гарантией
обработки списка любого размера. Общая очередь сохраняет прежние настройки.

Laravel callback `failed()` вызывает `FailRankCheckService` с command по ID. Сервис блокирует
агрегат в транзакции, переводит только `PARSING` в `FAILED`, обновляет `Impression` и сохраняет
событие через repository. При терминальном таймауте Laravel откатывает текущую транзакцию до
callback, поэтому частичные строки не сохраняются. `READY` и `FAILED` не обрабатываются повторно,
агрегат отклоняет такой вызов через `UnableToProcess`. Отсутствующий запуск при финальном сбое
отклоняется через `RankCheckNotFound`. Эти проверки сохраняют выбранное владельцем поведение.

Регрессионные тесты проверяют реальную постановку queued listener, согласованность лимитов,
откат частичных строк через `Job::fail(TimeoutExceededException)`, сохранение `FAILED` и
неизменность завершённого результата при отклонении повторной доставки. Исходный CSV проверки №20
недоступен локально, поэтому время его обработки и конкретный участок таймаута не измерены.

Для списка примерно на 2000 строк добавлен `RankCheckSimilarityMatcher` port и Infrastructure
adapter `PromptRankCheckSimilarityMatcher`. Подсказки загружаются один раз на batch, группируются
по metaphone и обходятся для поиска минимального расстояния без сортировки. Подбор кандидатов
кэшируется в пределах batch по metaphone, повторные строки ищутся один раз. Сохраняются пороги
2 для metaphone и 5 для prepared line, первая подсказка при равном расстоянии и приоритет
точного совпадения. Corpus больше не хранится в static поле живущего между задачами воркера,
а идентификационные строки не записываются в лог. Количество и порядок строк результата не меняются.

Синтетический замер для 2000 входных строк и 10 000 подсказок дал 46,694 секунды для прежнего
поиска и 0,556 секунды для нового. Оба алгоритма вернули одинаковую карту идентификации.
Замер использовал искусственные идентификаторы и metaphone, поэтому не предсказывает полное
время production-проверки. Corpus загружается одним запросом на batch; новых запросов на
каждую входную строку поиск не добавляет.

## Объединение идентификации после слияния веток, 2026-10-03

Проверки разрядов и идентификация протокола используют общий `PromptIdentifier` и
`StandardPromptIdentifier` из Domain/PersonPrompt. `identPerson()` возвращает ID или `null`,
`match()` возвращает карту найденных ID для уникальных входных строк. Точное совпадение
по-прежнему имеет приоритет в `StandardRankCheckPersonMatcher`.

Индекс подсказок и кэш ближайших фонетических групп живут в scoped экземпляре идентификатора,
сбрасываемом Laravel между заданиями. Сохранены пороги 2/5, выбор первой записи при равенстве,
отсечение по длине и отсутствие загрузки индекса для пустого batch. Числовые фонетические
ключи защищены строковым префиксом. Отдельные `RankCheckSimilarityMatcher` и
`PromptRankCheckSimilarityMatcher` удалены вместе с binding; тесты перенесены в Domain.
