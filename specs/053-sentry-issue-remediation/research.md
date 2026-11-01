# Research

## Startup response failure, FC/FD

Decision: выключить display_errors и display_startup_errors в production ini,
включить log_errors. Не фильтровать Sentry и не модифицировать vendor.

Evidence: оба events одного POST/trace, FD возникает в public/index.php при send(),
FC возникает при попытке handler отправить ответ на исходную ошибку. PHP 8.5.10
FPM. Breadcrumbs не раскрывают источник преждевременного вывода.

Локальный PHP 8.5.10 с output_buffering=0, display_errors=1 и настоящим entrypoint:
POST с Content-Type multipart/form-data без boundary сначала выводит PHP Request
Startup warning, затем Symfony Response.php:328 выдаёт те же warning и fatal.
Первый эксперимент имел ошибочный cwd router; он не используется как app evidence.
Повтор с cwd public воспроизвёл именно Response.sendHeaders failure.

Rationale: startup output возникает раньше bootstrap и не исправляется настройкой
Laravel. Symfony при headers_sent всё ещё отправляет status header для HTTP SAPI.
Контекст поведения upstream: https://github.com/symfony/symfony/issues/60603.
Точный исходный body отсутствует, multipart является воспроизведением вероятной
причины, а не доказанным payload production.

Rejected: глобальный ignore, подавление ErrorException, блокировка конкретного URL,
понижение Symfony, output buffer как маскировка вывода. Они скрывают причину/ошибки.

## Group query growth, F6

Decision: до цикла загрузить distinct normalized names через существующий
GroupRepository::byCriteria(names), сохранить первое совпадение, создавать отсутствующие
группы без индивидуального SELECT. Очищать group/distance caches при каждом create.

Evidence: event query active + normalize_name + limit 1, transaction ParseEventProtocolHandler.
Текущая фабрика кэширует только одинаковые имена; каждое distinct имя делает SELECT.
Существующий repository уже умеет whereIn names и единый GroupNameNormalizer.

Rejected: кэш между jobs, новый repository API, batching всех distance queries.
Последнее выходит за конкретный обнаруженный query pattern и требует иной lock policy.

## Repeated person cleanup, FB

Decision: локально обработать PersonNotFound при cleanup прежнего спортсмена.

Evidence: active-only lockById возвращает null после уже выполненного disable;
handler вызывает cleanup снова при повторной доставке. Аналогичная обработка
уже есть в reassignment ranks и event cleanup.

Rejected: делать все DisablePersonService вызовы молча успешными, ловить Throwable,
выключать Person напрямую. Они меняют публичный контракт и скрывают реальные сбои.

## Historical schema and DI, F0/F7/F8/F9/FA

Decision: сохранить существующие migration/collector/bindings, проверить их
регрессии и документировать migrate-before-workers deployment.

Evidence: миграция 2026_10_17_000001 добавляет обе колонки; исторические SQL failures
возникали 18 октября. F7 null bool согласуется с отсутствующей колонкой, но доступных
данных недостаточно для окончательного вывода. F0 binding уже существует в EventProvider.
Текущие production migration status и загруженный код workers недоступны через Sentry.

Rejected: ?? false в collector и try/catch SQL unknown column. Это маскирует сломанную
схему и может молча исказить разряды. Исторически resolved не гарантирует отсутствия
текущего code defect, поэтому FB исправляется отдельно.
