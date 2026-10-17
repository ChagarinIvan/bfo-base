# План реализации: ручная активация I разряда

**Branch**: `master` | **Date**: 2026-10-17 | **Spec**: [spec.md](spec.md)

## Summary

Убрать I разряд из правила автоматической активации в `Rank`. Парсер перестанет проставлять дату новым строкам, а калькулятор перестанет выводить дату из факта выполнения. Старые активированные строки уже содержат дату в `protocol_lines.activate_rank` и сохраняют её при пересчёте. Действующие ручные API и пересчёт используются без изменения контракта.

## Technical Context

**Language/Version**: PHP 8.5  
**Primary Dependencies**: Laravel 13, PHPUnit 13  
**Storage**: MySQL 8.4, `protocol_lines`, `person_rank_histories`, `person`  
**Testing**: узкие domain и integration тесты, затем итоговые проверки проекта  
**Target Platform**: сервер обработки протоколов, API активации, консольный refill  
**Project Type**: Laravel приложение  
**Performance Goals**: без дополнительных запросов при обработке протокола и пересчёте
**Constraints**: прежние активированные I разряды сохраняются; изменение II и юношеских разрядов отложено  
**Scale/Scope**: политика активации I разряда; без изменения схемы и исторических данных

## Constitution Check

Проверено до исследования и после проекта. Правило находится в чистом `Domain/Rank/Rank`; обработка, расчёт и API уже используют единый Application путь. Новые события и транзакции для изменения правила не требуются: запись новой строки и ручная активация остаются в существующих сценариях. Интеграционные тесты проверяют сохранение старой даты и пересчёт. Публичный контракт API не меняется.

## Project Structure

```text
specs/046-first-rank-activation/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/activation.md
├── quickstart.md
├── checklists/requirements.md
└── tasks.md

app/Domain/Rank/Rank.php
tests/Domain/Rank/RankTest.php
tests/Domain/Person/RankCalculatorTest.php
tests/Feature/Rank/FirstRankActivationTest.php
```

## Complexity Tracking

Дополнительных слоёв, таблиц, контрактов и миграций нет.
