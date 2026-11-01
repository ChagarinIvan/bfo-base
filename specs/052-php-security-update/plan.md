# Implementation plan: PHP security update

**Branch**: 052-php-security-update | **Date**: 2026-11-01 | **Spec**: [spec.md](spec.md)

## Summary

Подготовить серверное обновление PHP 8.5.12, обновить все совместимые Composer
packages и вернуть полный комплект артефактов в PR #178. Локальный PHP сохраняется.

## Technical context

Server/CI PHP 8.5.12, local PHP 8.5.10, Laravel 13, Composer 2.10.3,
MySQL 8.4, Redis 8, существующая Vue SPA. Новые зависимости напрямую не добавляются.
composer.json constraints сохраняются, resolver работает на реальном локальном PHP.
После последующего поручения пользователя все локальные гейты выполняются
на установленном PHP 8.5.10; существующие CI jobs PHP 8.5.12 сохраняются.

## Constitution check

PASS для области реализации. Обновление идёт в рамках разрешённых constraints;
бизнес-поведение моделей, write/read services, repositories, transactions и events не меняется.
Допустимы точные исправления type metadata и тестов при новых ошибках статического анализа.
Нет изменений сохранения, Impression или удаления. Новых N+1 queries не добавляется.
Последнее поручение пользователя требует полного Definition of Done: CS, STAN,
Rector dry-run, PHP tests и frontend CI должны пройти после механических правок.

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

## Rector compatibility and final gates

Применить механические предложения нового Rector к 19 PHP-файлам, сохранить
существующие бизнес-ветки и типы значений. Удалить два deprecated skips, которые
больше не регистрируются. Для ProtocolFactory оставить точечное исключение
ExplicitNullableObjectInstanceRector: существующая null-проверка не добавляет
конкретный HTTP UploadedFile в Domain. Остальные правила/пути не отключаются.
После автоматической правки выполнить cs-fix, review diff и все финальные гейты.
Локальный runtime не переустанавливается. Server image собирает пользователь.
