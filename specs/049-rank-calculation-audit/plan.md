# Implementation Plan: простой аудит применений разряда

**Branch**: `049-rank-calculation-audit` | **Date**: 2026-10-25 | **Spec**: [spec.md](spec.md)
**Input**: уточнённая 049 и [19 замечаний ревью](review.md). Уточнение пройдено без вопросов: значимые решения уже заданы пользователем.

## Summary

Person::updateRanks принимает причину, изменяет пять полей проекции, updated и спортивную историю, записывает PersonRanksUpdated и добавляет чистый RankAuditAction в список ожидающих сохранения. EloquentPersonRepository сохраняет проекцию, заменённую историю и новые действия в общей транзакции. Прежние snapshots, equality/dedup, failed entries, correction preview/apply удаляются. Чтение возвращает DTO с обязательным applied, UI показывает последовательность результатов.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript.
**Primary Dependencies**: Laravel 13, Vue 3, PrimeVue; новых зависимостей нет.
**Storage**: MySQL 8.4; Redis 8 для существующей очереди.
**Testing**: PHPUnit для чистых значений/command orchestration и persistence/API; Vitest для интерфейса.
**Target Platform**: существующее приложение Linux/PHP и SPA.
**Project Type**: web application с CLI и queue workers.
**Performance Goals**: одна индексированная выборка perPage+1 для журнала, без COUNT и запроса Person перед списком; без запросов автора для каждой записи. Полный пересчёт читает ID потоково.
**Constraints**: сохранять существующие расчётные исправления, правила активации, права и after-commit обработку; не менять исходные факты и старый аудит при rebuild.
**Scale/Scope**: одна фича 049, одна таблица аудита, один защищённый список; область массового rebuild остаётся прежней, активные спортсмены.

## Constitution Check

- Aggregate: существующий Person владеет проекцией, историей и ожидающими действиями. Новые RankAuditAction и его PersonRank являются чистыми типизированными значениями. Не вводим Eloquent-модель в Domain или новую ссылку Domain на Infrastructure.
- Command: причины передаются явно с точек запуска; обычный rebuild берёт текущую дату из Clock в сервисе. Read command criteria использует property hook.
- Transaction/lock: Application блокирует Person и выполняет расчёт/сохранение в транзакции. Импорт сохраняет существующую границу на спортсмена. Проекция, история и аудит откатываются вместе.
- Impression/event: updateRanks сохраняет updated и PersonRanksUpdated; created действия совпадает с Impression применения. Существующая after-commit доставка не меняется.
- Persistence: Infrastructure model хранит JSON только пяти применённых значений. PersonRepository сохраняет pending actions и очищает их после успешной записи. Read-only RankAuditRepository не собирает состояние и не пишет аудит.
- Errors: стандартные исключения доходят до диагностики, без RankRebuildFailure/catch/recordFailure.
- Deletion: история производная и заменяется; аудит технический append-only без FK на удаляемые строки истории/протокола, при rebuild не удаляется. Неактивный Person скрыт query журнала.
- ProtocolLine: backfill created/updated из Event через Distance, затем NOT NULL; стандартный ImpressionCast. Новые строки получают автора импорта.
- Переходный долг: существующий Eloquent Person/PersonRankHistory остаётся; pending audit повторяет существующую границу передачи истории в repository, не добавляя фреймворк в новые значения. Разделение Person остаётся REF-13 манифеста.
- DoD: сценарии и узкие тесты каждого шага, затем однократно полный backend/frontend CI, PHPStan/CS/Rector, HTTP smoke и проверка отсутствия новых N+1. Запись 049 в обновлениях изменяется, вторая не добавляется.

Проверка до исследования и после дизайна пройдена. Новых архитектурных исключений нет.

## Project Structure

- app/Domain/Person/Audit/RankAuditAction.php: personId, trigger, created, current: PersonRank, nextActivationOn, технический id. Применённое состояние обязательно; RankAuditTrigger содержит шесть бизнес-причин.
- app/Domain/Person/Person.php: pending rankAudit и updateRanks с причиной; выдача списка и очистка после сохранения. Domain PersonRanksUpdater остаётся коротким общим расчётом без audit repository.
- app/Infrastructure/Laravel/Eloquent/Person/RankAudit/: RankAuditModel с прикладным JSON и явным преобразованием в чистый action; EloquentRankAuditRepository только читает. Отдельный mapper снимков удаляется.
- app/Infrastructure/Laravel/Eloquent/Person/EloquentPersonRepository.php: сохранение pending действий после проекции/истории, очистка после успеха.
- app/Application/Dto/Person/RankAudit/: RankAuditDto, AppliedRankDto, assembler без массивов смешанного состава.
- app/Application/Service/Person/: rebuild с Clock, явные причины массового сценария, удаление CorrectPersonRanks*. Read service без предварительного запроса Person.
- app/Bridge/Laravel/: jobs требуют исходный trigger; refill передаёт Manual, daily CLI Scheduled; correction CLI удаляется.
- resources/spa/api/personRankAudit.ts, pages/persons/PersonRankAudit.vue: обязательный applied и список результата каждого применения, без before/after diff и failed states.
- database/migrations/2026_10_24_000001_add_rank_audit.php: id/person_id/trigger/created_at/created_by/applied JSON NOT NULL, индекс person_id/id; backfill ProtocolLine. Миграция фичи ещё не выпускалась, изменяется на месте.
- tests/: чистые action/command tests, integration audit/rollback/refill/backfill, API list и SPA states.

## Delivery

Сначала foundation записи успешных применений, затем переключение сценариев и query/API/UI, затем полный refill и удаление коррекции. Каждая фаза проверяет независимый результат. Старые задачи сохранены в [tasks-history.md](tasks-history.md), текущие T029–T046 выполнены. Изменения рабочих данных или запуск полного rebuild на рабочей базе не входят в выполнение.

## Итоговая проверка 2026-10-25

Полный backend-набор прошёл: 694 теста, 5000 assertions, exit 0, 2:37. В полном запуске было 16 PHPUnit notices; после него новое предупреждение из теста команды устранено заменой mock на stub. Узкий повтор этих двух Application test classes: 4 теста, 28 assertions, без notices. Остальные notices относятся к прежнему набору. Повтор полного набора понадобился после обнаруженного им пропуска Impression при объединении событий; этот сценарий исправлен и подтверждён 9 узкими тестами.

Полный frontend CI: 85 файлов, 264 теста; lint/typecheck/build прошли. Полные PHPStan, CS и Rector dry-run прошли; последние исправленные файлы дополнительно проверены узко теми же инструментами. Локальный HTTP справочника разрядов вернул 200. Зарегистрирован один rank-audit endpoint и два существующих CLI refill/rebuild-expired; correction удалён. Чтение журнала использует один индексированный JOIN и perPage+1 без COUNT/запросов в цикле.

Acceptance проверены: общая активация и регрессии, повторные применения, все пять полей applied, rollback проекции/истории/аудита, отсутствие дубля при обычном save, доступ/пустые результаты/пагинация, UI retry/race, backfill старых строк и новые строки объединения, полный refill с сохранением источников и прежнего аудита. Документационные ссылки и git diff --check прошли. Качество требований: 20/20; задачи новой редакции: 18/18. Рабочие данные и полный rebuild рабочей базы не запускались.
