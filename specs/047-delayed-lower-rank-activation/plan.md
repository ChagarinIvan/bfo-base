# Implementation Plan: отложенная активация младших разрядов

**Branch**: `master` | **Date**: 2026-10-17 | **Spec**: [spec.md](spec.md)

## Summary

Для новых строк пяти разрядов хранить признак месячного правила. Калькулятор активирует факт через календарный месяц от даты выполнения или по допустимой ручной дате. Проекция спортсмена хранит ближайший ожидающий срок; ежедневное задание выбирает его вместе с истечением текущего разряда. Старые строки сохраняют правило и даты.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript  
**Primary Dependencies**: Laravel 13, Carbon, PHPUnit 13, Vitest  
**Storage**: MySQL 8.4, `protocol_lines`, `person`, `person_rank_histories`  
**Testing**: Domain unit, parser, integration/API request, итоговые проверки  
**Target Platform**: импорт протоколов, API V1, ежедневная команда, SPA история  
**Project Type**: Laravel сервер и SPA  
**Performance Goals**: отбор сроков индексом; без полного ежедневного обхода  
**Constraints**: только новые протоколы; старые записи не изменять; вычисленный IIIю немедленный  
**Scale/Scope**: пять разрядов, два API действия, одна проекция и ежедневный пересчёт

## Constitution Check

Проверено до исследования и после дизайна. Правило находится в Domain `RankCalculator`, проверка ручной даты в Domain `ProtocolLine::activateRank()`, сохранение и отбор в Infrastructure. Агрегат `Person` владеет проекцией текущего разряда и срока; `ProtocolLine` хранит факт, а не вычисленное состояние. Ручное изменение использует существующий command, lock, transaction, `ProtocolLineRankActivated` и handler after commit. Пересчёт записывает `PersonRanksUpdated` и `updated` с системным актором. Чтение новых фактов использует eager loading, ежедневный отбор индексирован. Старые строки не мигрируются. Временные строки могут удаляться при замене протокола, после чего пересчёт удаляет производную историю. Переходное исключение аудита `ProtocolLine` описано в `research.md` (REF-05).

## Project Structure

```text
specs/047-delayed-lower-rank-activation/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/activation.md
├── quickstart.md
├── checklists/requirements.md
└── tasks.md

app/Domain/Rank/Rank.php
app/Domain/Person/{RankFact,RankCalculator,PersonRankState,Person}.php
app/Domain/ProtocolLine/{ProtocolLineInput,Factory/StandardProtocolLinesFactory}.php
app/Domain/Event/StandardProtocolParser.php
app/Application/Service/Person/{ActivatePersonRankService,UpdatePersonRankActivationDateService}.php
app/Infrastructure/Laravel/Eloquent/{Rank/EloquentRankFactsCollector,Person/EloquentPersonRepository}.php
app/Bridge/Laravel/Console/Commands/RebuildExpiredPersonRanksCommand.php
database/migrations/
tests/Domain/Person/RankCalculatorTest.php
tests/Feature/{Rank,Api/V1/PersonRankHistory}/
resources/spa/pages/updates/{updates.ts,updates.test.ts}
```

**Structure Decision**: доработать существующий импорт, чистый расчёт и ежедневный пересчёт без новых routes и экранов.

## Complexity Tracking

Новых нарушений принципов нет. Схема расширяется двумя полями: одно отличает новые строки без исторической переработки, второе делает выборку наступивших сроков ограниченной и индексированной.
