# Implementation Plan: Параметры дистанции на странице этапа

**Branch**: `master` | **Date**: 2026-10-11 | **Spec**: [spec.md](spec.md)

**Input**: Пункт 3 `plan.txt` и спецификация `specs/044-event-distance-details/spec.md`.

## Summary

Показать длину и количество КП выбранной группы рядом с выбором дистанции на публичной странице этапа. Страница уже получает оба значения для всех дистанций события. Достаточно вычислять текущую дистанцию по выбранному ID и форматировать длину из метров в километры; новые запросы и серверные изменения не нужны.

## Technical Context

**Language/Version**: TypeScript, Vue 3; действующий сервер PHP 8.5 / Laravel 13

**Primary Dependencies**: PrimeVue 4, текущие `getEventDistances` и тип `Distance`

**Storage**: Существующие дистанции, без миграций и записей

**Testing**: Vitest 3 для страницы этапа, TypeScript, ESLint, Prettier; финальные проверки проекта

**Target Platform**: SPA в браузере, публичная страница этапа

**Project Type**: Web application

**Performance Goals**: Без дополнительного запроса при выборе группы и без нового запроса при показе параметров

**Constraints**: Сохранить текущий выбор группы, загрузку результатов, обработку статуса протокола и пустые состояния

**Scale/Scope**: Один SPA-компонент и его тесты; существующий контракт чтения дистанций не меняется

## Constitution Check

- Изменение только представляет существующие данные. Не затрагивает агрегаты, команды, транзакции, доменные события, `Impression updated` и политику удаления.
- Новый код не добавляется в legacy `app/Services` или `app/Repositories` и не меняет направление зависимостей.
- Изменение поведения покрывается тестом страницы: начальный выбор, смена группы, отсутствие дистанции и нулевые значения.
- SPA сохраняет существующий V1 контракт. Дополнительные запросы, новые N+1 и изменение авторизации не появляются.

## Phase 0: Research

Решения и отвергнутые варианты записаны в [research.md](research.md). Неопределённостей, требующих нового API или уточнения бизнес-правил, нет.

## Phase 1: Design and contracts

- [data-model.md](data-model.md) фиксирует уже существующие поля дистанции и выбранный ID.
- [contracts/event-distance-details.md](contracts/event-distance-details.md) описывает видимый результат и неизменный контракт чтения.
- [quickstart.md](quickstart.md) описывает сквозную проверку.

## Post-design Constitution Check

Изменение остаётся в SPA. Серверные правила агрегатов и V1 не затронуты; выбранная дистанция берётся из уже загруженного списка.

## Project Structure

### Documentation (this feature)

```text
specs/044-event-distance-details/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/event-distance-details.md
├── quickstart.md
├── checklists/requirements.md
└── tasks.md
```

### Source Code (repository root)

```text
resources/spa/pages/events/EventViewPage.vue
resources/spa/pages/events/EventViewPage.test.ts
resources/spa/api/types.ts                          # existing Distance contract, read only
app/Application/Dto/Distance/ViewDistanceDto.php    # existing V1 response, read only
```

**Structure Decision**: Использовать данные текущего SPA-запроса и сохранить серверный API без изменений.
