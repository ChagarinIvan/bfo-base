# Implementation plan: Impressions строк протокола

**Branch**: `master` | **Date**: 2026-10-27 | **Spec**: [spec.md](spec.md)

## Summary

Добавить обязательные created_at/created_by/updated_at/updated_by в protocol_lines.
Миграция проверяет связи до DDL, переносит обе пары из events через distances
одним UPDATE и устанавливает NOT NULL. Фабрика получает Clock и автора текущего
импорта; Event.parse передаёт автора из имеющегося Impression. Три существующих
перехода сохраняют updated. Публичный контракт сохраняется.

## Technical context

- PHP 8.5, Laravel 13, MySQL 8.4; новые зависимости не нужны.
- PHPUnit 13, integration-тесты с MySQL и отдельной SQLite-базой для up/down.
- Область: одна миграция, существующая модель и фабрики, вызов Event.parse,
  соответствующие тесты, манифест и запись обновлений.
- Дополнительные запросы при создании строк не допускаются. Backfill выполняется
  массово; preflight читает только идентификатор первой повреждённой строки.

## Constitution check

Проверено до и после проектирования.

- Сценарии и транзакции Application сохраняются. Новый command не требуется.
- Clock внедряется через конструктор; миграция использует DatabaseManager,
  без фасадов. Автор передаётся явно, без auth/container в бизнес-правилах.
- ProtocolLine владеет аудитом; существующие domain events сохраняются.
  Технический импорт и backfill не создают дополнительных business events.
- Обработчики и after-commit политика не меняются; повторный save не дублирует event.
- Физическое удаление импортных производных данных сохраняется.
- Модель остаётся переходной Eloquent-моделью REF-13. Mapping impressions через
  существующий ImpressionCast допустим только в её текущем persistence mapping;
  новые бизнес-правила не используют Laravel. Выделение mapper и чистой модели
  остаётся отдельным ограниченным шагом REF-13.
- Unit Application/Domain не создают Eloquent. Новые проверки с реальными моделями
  располагаются в tests/Feature/ProtocolLine.
- Итоговые гейты выполняются один раз в конце; исправления после найденных ошибок
  проверяются повторно только необходимыми командами.

## Project structure

- database/migrations/2026_10_27_000001_add_protocol_line_impressions.php
- app/Domain/ProtocolLine/ProtocolLine.php
- app/Domain/ProtocolLine/Factory/{ProtocolLinesFactory,StandardProtocolLinesFactory}.php
- app/Domain/Event/Event.php
- database/factories/Domain/ProtocolLine/ProtocolLineFactory.php
- tests/Feature/ProtocolLine/{ProtocolLineImpressionsMigrationTest,ProtocolLineImpressionsTest,StandardProtocolLinesFactoryTest}.php
- tests/Feature/{Rank/DelayedRankActivationTest,Cup/ElkPathCupEliteGroupsTest}.php
- app/Infrastructure/Laravel/Eloquent/Event/EloquentUniteEventDataService.php
- tests/Feature/Event/UniteEventsTest.php
- .specify/memory/backend-architecture-manifest.md
- resources/spa/pages/updates/{updates.ts,updates.test.ts}

## Delivery phases

1. Историческая миграция и её проверки, FR-001–004, FR-009, FR-011.
2. Mapping и фабрики с явным автором, FR-005–006.
3. Существующие переходы и persistence-тесты, FR-007–008, FR-010.
4. Регрессии импорта, документация, финальные гейты и converge.

## Review

Спецификация и план проверены по конституции и манифесту в рамках запроса
пользователя пройти весь цикл. Блокирующих расхождений и открытых вопросов нет.

## Regression review

Полный suite выявил второй путь создания строк, объединение событий.
Eloquent replicate удаляет timestamps, поэтому финальная копия должна получить
created = updated = newEvent.created. Этот Impression уже создаётся через Clock
в StandardEventFactory с автором объединения. Новые события или бизнес-правила
объединения не требуются. Регрессия проверяется в UniteEventsTest и API-тесте.
