# Implementation plan: Person rank calculation metadata

**Branch**: master, сохранена | **Date**: 2026-11-01 | **Spec**: [spec.md](spec.md)

## Summary

Добавить атомарный audit последнего успешного расчёта Person, закрытый enum причины,
передачу источника по всем существующим сценариям и auth-only поля PersonView.

## Technical context

PHP 8.5 / Laravel 13 / MySQL 8.4; PHPUnit, PHPStan, CS Fixer, Rector;
TypeScript/Vitest. Локальный PHP без Docker. Новых dependencies нет.
Metadata читаются с Person без relations и дополнительных запросов.

## Constitution check

До research и после design PASS, конституция 2.6.0.

- Aggregate Person, инвариант rank state + metadata + updated + PersonRanksUpdated.
- Существующие commands получают typed reason, Domain mutation обязана принять его.
- Lock/transaction принадлежат существующим services, projection/history сохраняются
  через существующий port/adapter. Никаких новых query методов и legacy-классов.
- Event updater вызывается в существующей Event transaction, handlers остаются after-commit.
- No-op по rank всё равно успешный расчёт с новым audit; failed calculation не сохраняется.
- Soft disable/delete policies не меняются; PersonRankHistory остаётся производной проекцией.
- API camelCase, общий serializer, authenticated groups, class-level @see request tests.
- Unit Application использует mocks, реальные записи только Feature integration tests.
- Переходный Eloquent Person REF-13 не расширяется framework зависимостями; чистая
  модель/mapper остаётся отдельной ограниченной задачей реестра.

## Phase 0

[research.md](research.md) содержит найденные callers, причины, совместимость очереди
и решения хранения. Неопределённостей нет; уточнения пользователя внесены в spec.

## Phase 1

[data-model.md](data-model.md), [contracts/person-view.md](contracts/person-view.md),
[quickstart.md](quickstart.md). Spec и plan review PASS, hooks отсутствуют.

## Project structure

- app/Domain/Person/RankCalculationReason.php и Person.php
- database/migrations/2026_11_01_000001_add_person_rank_calculation_metadata.php
- app/Application/Service/Person/Rebuild{PersonRanks,ExpiredPersonRanks}*.php
- app/Domain/Person/StandardEventPersonRankUpdater.php
- app/Application/Service/{Event/CleanupEventResultsService,ProtocolLine/SetPersonToProtocolLinesService}.php
- app/Bridge/Laravel/{Jobs,Console/Commands,Http/Controllers/Api/V1/Person}/
- app/Application/Handler/Rank/ProtocolLineRankActivatedHandler.php
- app/Application/Dto/Person/{ViewPersonDto,PersonAssembler}.php
- rector.php, две локальные защиты default для старых serialized jobs
- resources/spa/api/types.ts, resources/spa/pages/updates/updates{,.test}.ts
- tests/Feature/Person/PersonRankCalculationTest.php и PersonRankCalculationMigrationTest.php
- tests/Feature/Api/V1/Person/PersonRankCalculationViewTest.php
- существующие tests/Application/Service/Rank, tests/Feature/Rank,
  tests/Feature/Person, tests/Bridge/Laravel и связанные API/cleanup tests

## Complexity tracking

Новых архитектурных исключений нет, нового endpoint/UI нет. Generic jobs используют property default reason и сохраняют
совместимость со старыми serialized payload, server выбирает reason без request input.

## Уточнение 2026-11-06

Spec/plan review: причины разделены по бизнес-сценариям по запросу пользователя.
Сохранить Protocol для старых данных и очереди без backfill; добавить три enum cases,
обновить callers, API union и регрессии каждого сценария. Схема БД не меняется,
значения помещаются в varchar(32). Транзакции, запросы и алгоритм расчёта прежние.

## Обязательная причина 2026-11-06

Spec/plan review PASS по запросу пользователя: изменить исходную миграцию,
NOT NULL reason с временным default unknown, затем снять default. Person задаёт
Unknown при создании; дата остаётся nullable. Обновить DTO/assembler/API union
и тесты миграции: backfill, NOT NULL, отсутствие default, rollback.
