# Implementation Plan: Backend architecture manifest

**Branch**: `master` | **Date**: 2026-09-27 | **Spec**: [spec.md](spec.md)

**Input**: Документационный аудит бэкенда и обязательное архитектурное руководство для новых фич и ревью.

## Summary

Зафиксировать наблюдаемую архитектуру, целевые правила и приоритетный долг в одном манифесте `.specify/memory/backend-architecture-manifest.md`. Внести короткую обязательную ссылку в конституцию и инструкции агентов. Код приложения, миграции и тесты не меняются.

## Technical Context

**Language/Version**: PHP 8.5, Laravel 13; Markdown для артефактов.
**Primary Dependencies**: Eloquent, Redis/Horizon, Sanctum, проектный Spec Kit.
**Storage**: Текущие MySQL-модели; новые данные и таблицы не создаются.
**Testing**: Проверка качества требований, ссылок, трассировки наблюдений и `git diff --check`; поведение приложения не меняется. Общие runtime-гейты конституции остаются отдельной проверкой перед завершением всей фичи и не объявляются пройденными этим аудитом.
**Target Platform**: Разработка и PR review в репозитории BFO Base.
**Project Type**: Документация и инструкции AI-агентов для бэкенда веб-приложения.
**Performance Goals**: Не применимо к документационной правке.
**Constraints**: Не менять исходный код; отделять факты от целевой политики; учитывать переходное состояние Domain.
**Scale/Scope**: Четыре целевых слоя, legacy-каталоги, основные предметные области и жизненный цикл событий.

## Constitution Check

*Gate до исследования и после дизайна: PASS.*

- Принцип I: цель чистого Domain сохраняется; Eloquent-модели в Domain названы переходным долгом.
- Принцип III: поведение приложения не меняется; манифест содержит требования к будущим тестам, но эта фича не создаёт тесты.
- Принцип VII: command, transaction, aggregate, audit, event и repository описаны как проверяемый путь записи.
- Принцип IX: манифест становится обязательным источником для AI-агентов и ревью. Конституция имеет приоритет.
- Нынешние нарушения перечисляются как долг, а не как разрешение повторять их.

## Project Structure

### Documentation (this feature)

```text
specs/040-backend-architecture-manifest/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── architecture-manifest.md
├── checklists/
│   ├── requirements.md
│   └── architecture.md
└── tasks.md
```

### Source Code (repository root)

```text
.specify/memory/constitution.md
.specify/memory/backend-architecture-manifest.md
AGENTS.md
CLAUDE.md
app/Application/             # исследуется, не меняется
app/Domain/                  # исследуется, не меняется
app/Bridge/                  # исследуется, не меняется
app/Infrastructure/          # исследуется, не меняется
app/Services/                # legacy, исследуется
app/Repositories/            # legacy, исследуется
tests/                       # существующие тестовые примеры
```

**Structure Decision**: Канонический документ живёт рядом с конституцией, а короткие обязательные ссылки находятся в обоих файлах инструкций AI-агентов. Обзор не дублирует спецификации старых фич.

## Phases

1. Сверить действующие пути записи/чтения, события и удаление; собрать доказательства в `research.md`.
2. Описать сущности документации, контракт и способ проверки в артефактах Phase 1.
3. Составить задачи по пользовательским сценариям и проверить согласованность артефактов.
4. Написать манифест и включить его в инструкции агентов, затем провести сверку и convergence.

## Complexity Tracking

Новых runtime-компонентов и отклонений от конституции нет.
