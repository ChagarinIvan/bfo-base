# Implementation Plan: Поиск этапа при добавлении в кубок

**Branch**: `master` | **Date**: 2026-10-11 | **Spec**: [spec.md](spec.md)

**Input**: Форма `/app/cups/{cupId}/events/create` должна показывать первые 20 вариантов по убыванию даты и искать на сервере по названию соревнования или этапа.

## Summary

Расширить существующий `GET /api/v1/events` проверенными параметрами `sortBy=date`, `sort=desc`, `competitionOrEventName`. Преобразовать сортировку в `Criteria`, а поиск выполнить в Eloquent query до `Slice`. Страница создания передаёт ввод фильтра `Select` серверу и показывает первые 20 результатов без пагинатора. Форма редактирования сохраняет локальный выбор.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript, Vue 3

**Primary Dependencies**: Laravel 13, PrimeVue 4, существующий V1 Events API и `Slice`

**Storage**: MySQL 8.4; миграции и записи не нужны

**Testing**: PHPUnit API request, Vitest SPA, TypeScript, ESLint, Prettier

**Target Platform**: Страница создания этапа кубка в SPA

**Project Type**: Laravel API и SPA

**Performance Goals**: Не более 21 строки на чтение через `Slice` (20 видимых + признак следующей), без загрузки всех этапов; один запрос списка на действие после задержки ввода

**Constraints**: Существующие фильтры `year` и `notRelatedToCup`, права доступа, создание CupEvent и форма редактирования сохраняются

**Scale/Scope**: Существующий GET Events API, запрос к Event repository, клиентская форма создания и общий компонент с отдельным режимом поиска

## Constitution Check

- Чтение остаётся в существующих Application command/DTO, Domain `Criteria` и Infrastructure repository. Bridge action не содержит SQL.
- Новый параметр V1 валидируется и использует camelCase; request-тест с `@see` проверяет контракт и права.
- Изменение поведения получает API и SPA регрессионные тесты. Unit Application/Domain не создают Eloquent.
- Мутации агрегатов, транзакции, события, аудит и политика удаления не затронуты.
- Для запроса нужны только поля Event и имя Competition. Существующее eager loading Competition остаётся, нового N+1 нет.
- Запись в историю обновлений обязательна до закрытия фичи.

## Phase 0: Research

Решения и альтернативы: [research.md](research.md).

## Phase 1: Design and contracts

- [data-model.md](data-model.md) описывает существующие данные и параметры поиска.
- [contracts/cup-event-options.md](contracts/cup-event-options.md) фиксирует точные параметры запроса, заголовки и ошибки.
- [quickstart.md](quickstart.md) описывает проверку в браузере и узкие тесты.

## Post-design Constitution Check

Новых слоёв и записей нет. Проверенная сортировка попадает в `Criteria` только из DTO, а SQL находится в Eloquent adapter. Сервер фильтрует до пагинации.

## Project Structure

### Documentation

```text
specs/045-cup-event-option-search/{spec.md,plan.md,research.md,data-model.md,quickstart.md,tasks.md}
specs/045-cup-event-option-search/contracts/cup-event-options.md
specs/045-cup-event-option-search/checklists/requirements.md
```

### Source Code

```text
app/Application/Dto/Event/SearchEventDto.php
app/Application/Service/Event/ListEvents.php
app/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepository.php
tests/Feature/Api/V1/Event/ListEventsActionTest.php
resources/spa/api/{types.ts,events.ts,events.test.ts}
resources/spa/pages/cups/{CreateCupEventPage.vue,CreateCupEventPage.test.ts,CupEventForm.vue,CupEventForm.test.ts}
resources/lang/by.json
resources/spa/pages/updates/{updates.ts,updates.test.ts}
```

**Structure Decision**: Использовать действующий V1 endpoint и `Slice`; новый endpoint и отдельный repository не нужны.
