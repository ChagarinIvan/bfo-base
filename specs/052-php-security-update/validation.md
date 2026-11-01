# Validation: PHP security update

Дата 2026-11-01. Ветка 052-php-security-update, PR #178.
Исходный head ветки и refs/pull/178/head совпадали: abaa1250.
Дерево перед восстановлением недостающих частей было чистым.

## Фактическая область

В PR включены PHP image pin, startup ini, CI configuration, обновлённый lock,
README, запись updates и полный набор spec-kit. Локальный PHP повторно не обновлялся,
Docker локально не запускался. Production deploy выполняет пользователь.
composer.json сохраняет ^8.5; config.platform и ignore-platform flags не добавлялись.

## Выполненные проверки

| Проверка | Результат |
| --- | --- |
| composer update --with-all-dependencies --no-interaction --no-progress | exit 0, обновлены production/dev packages в текущих constraints |
| composer validate --strict | exit 0, composer.json valid |
| composer check-platform-reqs | exit 0 на фактическом PHP 8.5.10, требуемые extensions доступны |
| composer install --no-interaction --no-progress | exit 0, nothing to install/update/remove; SHA-256 lock не изменился |
| composer audit --locked --format=json | нет advisories; exit 1 из-за abandoned dev package |
| composer audit --locked --no-dev --format=json | нет advisories и abandoned packages |
| Workflow YAML parse | syntax OK |
| git diff --check | exit 0 |
| Review build inputs | server/CI 8.5.12; tracked ini применяется через COPY и существующий compose mount |

Полный audit сообщает symplify/rule-doc-generator-contracts, транзитивную dev-зависимость
последнего разрешённого driftingly/rector-laravel 2.7.3. Пакет требуется как ^11.2,
замена не указана. Warning не скрывался. Ручное удаление из lock не выполнялось.
Production install --no-dev этот пакет не устанавливает; CI production audit не
проверяет dev graph, полный результат приведён выше.

## Analyze и сверка реализации

10 FR, 5 SC, 3 user stories, 13 задач, checklist 16/16.
Все требования покрыты задачами. Несогласованных терминов, неразрешённых уточнений
и нарушений границ архитектуры в области изменения не найдено.

| Требования | Evidence |
| --- | --- |
| FR-001, SC-001, US1/AC1–2 | Dockerfile php:8.5.12-fpm, setup-php php-version 8.5.12 |
| FR-002, US1/AC3 | enviroment/php/99-security.ini, COPY в образ и CI ini-values; compose mount включает tracked файл |
| FR-003, US2/AC1 | Реальный composer update и diff composer.lock, таблица версий ниже |
| FR-004, SC-003, US2/AC2/4 | validate/platform/install; hash lock до и после install совпадает |
| FR-005, SC-002, US2/AC3 | Полный audit без advisories; dev warning отражён, production audit чист |
| FR-006, SC-005, US1/AC4 | Работа выполнена на имеющемся PHP 8.5.10, без повторного Homebrew/Docker и подмены платформы |
| FR-007 | Application/Domain/Bridge/Infrastructure source и схема БД не менялись |
| FR-008, US3/AC2 | README и quickstart с build/install/runtime check/restart |
| FR-009, SC-004, US3/AC1 | Полный каталог specs/052, запись updates, latest catalog assertion обновлён |
| FR-010, US3/AC3 | Этот validation разделяет выполненные проверки и отложенные suites/deploy |

Сверка spec/plan/tasks с текущими файлами не выявила недостающей реализации в
уточнённой области. Новые convergence tasks не добавлялись. Проверка действующего
server runtime и полных regression suites этим выводом не подменяется.

## Что не проверялось локально

По прямому уточнению пользователя не выполнялись полный composer test, STAN,
CS, Rector, frontend CI и повторная установка локального PHP. Ранее начатый
frontend прогон до отмены большого локального обновления завершился двумя
таймаутами на фоне сборки Homebrew; он не является результатом проверки нового lock.
Существующие полные CI jobs сохранены и запускаются на PHP 8.5.12.
Сборка Docker image, startup ini внутри нового контейнера и production runtime
требуют проверки при серверной выкладке. Ручной UI smoke не выполнялся.
Новых query implementations нет; runtime N+1 после vendor update отдельно не измерялся.

## Пакетный review

Обновлено 71 пакетов, добавлено 1, удалено 1. Прямые major constraints не расширялись.

| Package | До | После |
| --- | --- | --- |
| brick/math | 0.18.0 | 1.0.0 |
| carbonphp/carbon-doctrine-types | 3.2.0 | 3.2.1 |
| doctrine/inflector | 2.1.0 | 2.1.1 |
| doctrine/lexer | 3.0.1 | 3.0.3 |
| dragonmantank/cron-expression | v3.6.0 | v3.6.1 |
| guzzlehttp/guzzle | 7.15.5 | 7.16.1 |
| guzzlehttp/promises | 2.5.3 | 2.6.0 |
| guzzlehttp/psr7 | 2.13.1 | 2.13.2 |
| laravel/framework | v13.29.0 | v13.39.0 |
| laravel/horizon | v5.48.3 | v5.50.1 |
| laravel/prompts | v0.3.24 | v0.3.26 |
| laravel/serializable-closure | v2.0.16 | v2.1.0 |
| league/commonmark | 2.10.0 | 2.10.7 |
| league/flysystem | 3.35.3 | 3.36.0 |
| league/uri | 7.8.1 | 7.9.1 |
| league/uri-interfaces | 7.8.1 | 7.9.1 |
| monolog/monolog | 3.10.0 | 3.12.1 |
| nesbot/carbon | 3.13.2 | 3.15.1 |
| nette/schema | v1.3.6 | v1.4.0 |
| nette/utils | v4.1.5 | v4.1.6 |
| phpoffice/phpspreadsheet | 5.9.0 | 5.10.0 |
| predis/predis | v3.6.0 | v3.7.0 |
| ramsey/uuid | 4.9.3 | 4.9.4 |
| sentry/sentry | 4.31.0 | 4.35.0 |
| sentry/sentry-laravel | 4.27.0 | 4.29.0 |
| symfony/clock | v8.1.0 | v8.1.9 |
| symfony/console | v8.1.5 | v8.1.10 |
| symfony/css-selector | v8.1.5 | v8.1.10 |
| symfony/error-handler | v8.1.5 | v8.1.10 |
| symfony/finder | v8.1.5 | v8.1.10 |
| symfony/http-foundation | v8.1.5 | v8.1.10 |
| symfony/http-kernel | v8.1.5 | v8.1.10 |
| symfony/mailer | v8.1.5 | v8.1.10 |
| symfony/mime | v8.1.5 | v8.1.10 |
| symfony/options-resolver | v8.1.0 | v8.1.10 |
| symfony/polyfill-intl-grapheme | v1.41.0 | v1.43.3 |
| symfony/polyfill-intl-idn | v1.42.0 | v1.43.0 |
| symfony/polyfill-intl-normalizer | v1.42.0 | v1.43.0 |
| symfony/polyfill-mbstring | v1.38.2 | v1.43.3 |
| symfony/polyfill-php80 | v1.37.0 | v1.43.0 |
| symfony/polyfill-php83 | v1.41.0 | v1.43.0 |
| symfony/polyfill-php84 | v1.38.1 | v1.43.0 |
| symfony/polyfill-php85 | v1.41.0 | v1.43.0 |
| symfony/polyfill-php86 | v1.41.0 | v1.43.0 |
| symfony/polyfill-time | добавлен | v1.42.0 |
| symfony/polyfill-uuid | v1.37.0 | v1.43.0 |
| symfony/process | v8.1.5 | v8.1.10 |
| symfony/psr-http-message-bridge | v8.1.0 | v8.1.9 |
| symfony/routing | v8.1.5 | v8.1.10 |
| symfony/service-contracts | v3.7.1 | v3.7.3 |
| symfony/string | v8.1.2 | v8.1.10 |
| symfony/translation | v8.1.5 | v8.1.10 |
| symfony/uid | v8.1.5 | v8.1.9 |
| symfony/var-dumper | v8.1.5 | v8.1.10 |
| vlucas/phpdotenv | v5.7.0 | v5.8.0 |
| composer/semver | 3.4.4 | 3.6.0 |
| driftingly/rector-laravel | 2.6.1 | 2.7.3 |
| ergebnis/agent-detector | 1.2.0 | 1.3.0 |
| fidry/cpu-core-counter | 1.3.0 | 1.4.1 |
| friendsofphp/php-cs-fixer | v3.95.23 | v3.95.27 |
| larastan/larastan | v3.10.0 | v3.13.2 |
| nikic/php-parser | v5.8.0 | v5.9.0 |
| phpstan/phpstan | 2.2.9 | 2.3.4 |
| phpunit/php-code-coverage | 14.3.1 | 14.5.0 |
| phpunit/php-text-template | 6.0.0 | 6.0.1 |
| phpunit/phpunit | 13.3.2 | 13.4.5 |
| rector/rector | 2.6.4 | 2.8.3 |
| sebastian/environment | 9.3.2 | 9.3.3 |
| sebastian/git-state | 1.0.0 | 1.1.0 |
| sebastian/version | 7.0.0 | 7.0.1 |
| symfony/filesystem | v8.1.5 | v8.1.9 |
| webmozart/assert | 2.4.1 | 2.5.0 |
| theseer/tokenizer | 2.0.1 | удалён resolver-ом |

Composer.lock SHA-256 после install: `006a17c78aa84c65ed3593bf88091829063fc8aea061b6d250f0c0d51f05c23f`.
