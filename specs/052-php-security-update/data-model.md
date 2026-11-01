# Data model

Бизнес-сущности и схема БД не изменяются.

## Runtime

Server image php:8.5.12-fpm, CI PHP 8.5.12. Локальный PHP 8.5.10 сохраняется;
require.php ^8.5 без config.platform или ignore-platform flags.

## Startup configuration

Tracked enviroment/php/99-security.ini отключает session upload progress. COPY
обеспечивает image path, существующий compose mount сохраняет файл в conf.d.
Работающие FPM и queue workers получают новый runtime только после restart.

## Dependency lock

composer.lock обновляется resolver-ом для packages/packages-dev. Вручную версии
и checksums не редактируются. Install не меняет lock. Аудит различает security
advisories и abandoned packages; неподдерживаемый пакет находится в dev graph.
