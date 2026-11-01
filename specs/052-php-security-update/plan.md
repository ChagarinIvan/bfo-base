# Implementation plan: PHP security update

**Branch**: 052-php-security-update | **Date**: 2026-11-01 | **Spec**: [spec.md](spec.md)

## Summary

Подготовить серверное обновление PHP 8.5.12, обновить все совместимые Composer
packages и вернуть полный комплект артефактов в PR #178. Локальный PHP сохраняется.

## Technical context

Server/CI PHP 8.5.12, local PHP 8.5.10, Laravel 13, Composer 2.10.3,
MySQL 8.4, Redis 8, существующая Vue SPA. Новые зависимости напрямую не добавляются.
composer.json constraints сохраняются, resolver работает на реальном локальном PHP.
Полные локальные suites отменены пользователем; существующие CI jobs сохраняются.

## Constitution check

PASS для области реализации. Обновление идёт в рамках разрешённых constraints;
модели, write/read services, repositories, transactions и events не затрагиваются.
Нет изменений сохранения, Impression или удаления. Новых N+1 queries не добавляется.
Исключение из локального Definition of Done дано пользователем явно: полные
CS/STAN/Rector/test/frontend gates здесь не повторяются, состояние проверяется CI.

## Project structure

```text
Dockerfile
composer.lock
.github/workflows/laravel.yml
enviroment/php/99-security.ini
README.md
resources/spa/pages/updates/{updates.ts,updates.test.ts}
specs/052-php-security-update/
  spec.md, plan.md, research.md, data-model.md, tasks.md, quickstart.md
  checklists/requirements.md, contracts/runtime-and-dependencies.md, validation.md
```

## Phases and decisions

1. Сверить текущую ветку и refs/pull/178/head, сохранить список пакетов до update.
2. Сохранить Docker FROM php:8.5.12-fpm, добавить COPY tracked startup ini.
   Compose уже монтирует enviroment/php целиком, тот же файл виден через volume.
3. CI setup-php получает 8.5.12, intl и ini-values. Существующие suites остаются.
   Проверка production audit использует --no-dev, полный audit отдельно сохраняется
   в validation вместе с неподдерживаемой dev-зависимостью.
4. composer update --with-all-dependencies --no-interaction, без platform overrides.
5. validate, check-platform-reqs, audit, install с проверкой неизменности lock.
6. README, updates, полный набор spec-kit, analyze и ограниченный converge.
7. Закоммитить и push в ту же ветку; PR обновится автоматически.

## Complexity tracking

Транзитивный dev package symplify/rule-doc-generator-contracts abandoned и требуется
последним совместимым driftingly/rector-laravel. Не удалять его из lock вручную,
не расширять major constraints и не выдавать full audit за успешный.
