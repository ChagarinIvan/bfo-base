# Tasks: PHP security update

**Input**: spec.md, plan.md, research.md, data-model.md, contracts, quickstart.md.

## Phase 1: Setup and foundation

- [x] T001 Проверить ветку и refs/pull/178/head, зафиксировать исходное состояние в specs/052-php-security-update/validation.md.
- [x] T002 Проверить официальные источники в specs/052-php-security-update/research.md.
- [x] T003 Восстановить design artifacts и checklist в specs/052-php-security-update/.

## Phase 2: US1, runtime configuration

Independent test: server/CI build inputs задают PHP 8.5.12 и startup ini.

- [x] T004 [US1] Зафиксировать php:8.5.12-fpm в Dockerfile, FR-001.
- [x] T005 [US1] Добавить enviroment/php/99-security.ini и COPY в Dockerfile, FR-002.
- [x] T006 [US1] Синхронизировать runtime, ini и production audit в .github/workflows/laravel.yml, FR-001/002.

## Phase 3: US2, dependency update

Independent test: Composer validate, platform, audit и воспроизводимый install.

- [x] T007 [US2] Выполнить composer update --with-all-dependencies, обновить composer.lock и обозреть версии, FR-003/006.
- [x] T008 [US2] Проверить validate/platform и audit, записать dev warning в specs/052-php-security-update/validation.md, FR-004/005.
- [x] T009 [US2] Выполнить install и сравнить hash composer.lock, FR-004.

## Phase 4: US3, review and handoff

Independent test: ветка PR содержит все заявленные файлы и честный отчёт.

- [x] T010 [US3] Обновить README.md с server build/install/restart и границей локального runtime, FR-008.
- [x] T011 [US3] Добавить запись resources/spa/pages/updates/updates.ts и обновить updates.test.ts, FR-009.
- [x] T012 [US3] Выполнить analyze/converge и записать фактические результаты в specs/052-php-security-update/validation.md, FR-007/009/010.
- [x] T013 [US3] Закоммитить и отправить все файлы фичи в ветку PR #178, FR-009.

## Dependencies and execution order

T001 -> T002 -> T003; US1 и US2 независимы по файлам, US3 следует после них.
Install следует после update и audit. Commit/push выполняются после обзора diff.

## Parallel examples

Read-only research и проверка локальных build inputs независимы. Composer update
и восстановление документации можно выполнять параллельно, изменения lock только
через один процесс Composer. Полные локальные suites отменены пользователем.

## Implementation strategy

Сохранить исправленный Docker image, выполнить настоящий dependency update,
восстановить все артефакты, кратко проверить Composer и отправить в существующий PR.
Production deploy и локальное переустройство PHP не выполняются.

## Completion

Все 13 задач реализации и подготовки PR выполнены. Полные локальные гейты
отменены пользователем; CI/server verification не объявляется выполненной.
Evidence и ограничения в [validation.md](validation.md).
