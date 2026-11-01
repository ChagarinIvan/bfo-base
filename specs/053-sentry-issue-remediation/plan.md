# Implementation plan: Sentry issue remediation

**Branch**: master, не переключается | **Date**: 2026-11-01 | **Spec**: [spec.md](spec.md)

## Summary

Разобрать все 9 issues снимка. Исправить startup output, N+1 поиска групп и
неидемпотентную очистку Person. Для исторических ошибок схемы и DI проверить
текущие контракты и записать порядок выкладки. Статусы Sentry не менять.

## Technical context

- PHP 8.5, Laravel 13, Symfony HttpFoundation 8, PHPUnit 13, Vue/TypeScript/Vitest.
- MySQL 8.4 и Redis 8, существующие repository ports и transactions.
- Локальный PHP 8.5.10 без Docker; production образ уже закреплён на 8.5.12.
- Никаких новых зависимостей, таблиц или API. 9 issues за 90d, 3 unresolved.
- Цель производительности: один groups SELECT для 1/50 distinct names, ноль для пустого ввода.
- Startup HTTP-регрессия запускает локальный PHP subprocess с настоящим entrypoint,
  отключёнными внешними Sentry/Redis и production ini. Диагностика сохраняется в stderr.

## Constitution check

До research и после design: PASS, конституция 2.6.0.

- Изменяются существующие Domain factory и Application handler через ports;
  framework/query код не добавляется в Domain, legacy-каталоги не расширяются.
- Используется GroupRepository::byCriteria с names, нормализованными единым normalizer.
- Импорт сохраняет transaction/lock, Impression автора и создание через factories.
- Person cleanup остаётся after-commit и вызывает DisablePerson command/service,
  transactional soft disable и PersonDisabled. Только PersonNotFound в cleanup является no-op.
- Кэш очищается перед каждым create; события/updated повторно не генерируются при no-op.
- Новых API/DTO нет. Feature integration tests допустимы для реальных Eloquent записей.
- Существующий Eloquent в Domain не расширяется; REF-13 остаётся отдельной миграцией.
- Регрессии до исправлений, полный прогон в конце, запись updates и production limitations обязательны.

## Phase 0: Research

[research.md](research.md) фиксирует причины, альтернативы и эксперимент startup HTTP.
Исследования N+1 и resolved issues выполнены отдельными research agents по plan skill.
Неразрешённых проектных вопросов нет; неизвестные production-факты явно ограничены.

## Phase 1: Design

- [data-model.md](data-model.md): существующие модели, кэши и no-op transition.
- [contracts/remediation.md](contracts/remediation.md): HTTP, import, cleanup, deployment.
- [quickstart.md](quickstart.md): узкие проверки и выкладка без выполнения на production.
- [issue-inventory.md](issue-inventory.md): отдельный результат для каждого issue.

## Project structure

```text
enviroment/php/99-security.ini
app/Domain/ProtocolLine/Factory/StandardProtocolLinesFactory.php
app/Application/Handler/ProtocolLine/ProtocolLinePersonAssignedHandler.php
tests/Feature/Infrastructure/ProductionPhpResponseTest.php
tests/Feature/ProtocolLine/StandardProtocolLinesFactoryTest.php
tests/Feature/ProtocolLine/ProtocolLinePersonAssignedHandlerTest.php
resources/spa/pages/updates/updates.ts
resources/spa/pages/updates/updates.test.ts
specs/053-sentry-issue-remediation/
```

## Complexity tracking

Новых архитектурных исключений нет. Два HTTP issues объединены одной проверкой,
но оба ID сохраняются. Неподтверждённые production-причины не выдаются за доказанные.

## Review

Spec и plan согласованы с исходными событиями, scope и конституцией. Полный прогон
пользователем разрешён; промежуточные approvals повторно не запрашиваются.
