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
| FR-007 | Бизнес-поведение и схема БД не менялись; последующее исправление CI уточняет type metadata и устраняет повторный assertion |
| FR-008, US3/AC2 | README и quickstart с build/install/runtime check/restart |
| FR-009, SC-004, US3/AC1 | Полный каталог specs/052, запись updates, latest catalog assertion обновлён |
| FR-010, US3/AC3 | Этот validation разделяет выполненные проверки и отложенные suites/deploy |

Сверка spec/plan/tasks с текущими файлами не выявила недостающей реализации в
уточнённой области. Новые convergence tasks не добавлялись. Проверка действующего
server runtime и полных regression suites этим выводом не подменяется.

## Границы первоначальной проверки

При подготовке dependency update по уточнению пользователя не выполнялись полный
composer test, STAN, CS, Rector, frontend CI и повторная установка локального PHP.
Последующая проверка после сообщения об ошибках CI описана ниже. Ранее начатый
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

## Исправление PHPStan после CI

Обновлённые Larastan/PHPStan выявили две неточности, воспроизведённые узким
анализом EloquentGroupRepository.php и EventAggregateDispatchTest.php до правок.

- Group::distances() фактически возвращает HasMany; прежний union с Query Builder
  заменён точным HasMany<Distance, $this>, неиспользуемый import удалён. Runtime
  связи, foreign key и запрос withCount не изменились.
- В тесте отправки события удалена промежуточная проверка пустой очереди.
  Тест по-прежнему проверяет pending event до записи, два save, ровно одну отправку
  и пустую очередь после обоих сохранений. Нет повторного assertSame, который
  новый анализатор считает заведомо истинным из-за предыдущего сужения.
- Полный composer stan: exit 0, No errors, 968 файлов.
- EventAggregateDispatchTest, CupGroupFactoryTest и AbstractCupTypeDistanceTest:
  exit 0, 6 tests, 19 assertions на локальном PHP 8.5.10 и тестовой MySQL.
- PHP CS Fixer check двух изменённых PHP-файлов с явным config: exit 0.
- Нет ignore-комментариев, baseline entries, понижения уровня или изменения правил анализа.

На этапе исправления PHPStan остальные полные гейты ещё не повторялись. Исправления предназначены для
существующей ветки и PR #178; результат удалённого CI отдельно не заявляется.

## Итог после применения Rector и полного локального прогона

По последнему поручению пользователя применены предложения обновлённого Rector
и выполнены все локальные гейты. Это заменяет прежнее решение не запускать их.
Проверки выполнены на фактическом локальном PHP 8.5.10; CI/server остаются 8.5.12.

### Review механических изменений

- Rector изменил 19 файлов: явные instanceof для nullable объектов и упрощение
  nullable доступа. Дифф проверен, контракты методов и бизнес-ветки сохранены.
- PHP CS Fixer упорядочил новые imports в шести из этих файлов.
- Удалены deprecated skips FlipTypeControlToUseExclusiveTypeRector и
  StringToClassConstantRector, которые новый Rector больше не регистрирует.
- Для одного ProtocolFactory сохранено документированное исключение
  ExplicitNullableObjectInstanceRector: замена null-проверки добавляла бы прямую
  зависимость Domain factory от HTTP UploadedFile. Существующий транспортный долг
  не расширяется. Глобальные наборы и остальные пути не отключены.
- Новых запросов, миграций, API-полей или бизнес-операций не добавлено.

### Финальные результаты

| Gate | Результат |
| --- | --- |
| composer cs | exit 0, 0 из 1088 файлов требуют правки |
| composer stan | exit 0, 968 файлов, No errors |
| composer rector -- --dry-run | exit 0, нет новых diff и прежних deprecated warnings |
| DB_DATABASE=bfo_base_test composer test | exit 0, 698 tests, 5319 assertions |
| npm run ci | exit 0: lint/typecheck, 85 файлов, 283 теста, production build |
| git diff --check | exit 0 |

PHPUnit сохраняет прежние 15 notices, failures/errors нет. Vite сохраняет обычное
предупреждение о размере bundle; сборка проходит. Повторно обновлять локальный PHP
или запускать локальный Docker не потребовалось. Полные regression suites покрыли
затронутые ветки протоколов, разрядов, кубков и API/query tests, включая N+1 assertions.

Финальная сверка spec/plan/tasks и текущей реализации не выявила оставшейся
локальной работы в заданной области. Все 18 задач выполнены. Production deploy
и результат удалённого CI этим локальным прогоном не подменяются.

## Symfony advisories maintenance, 2026-11-06

CI failure воспроизведён через composer audit --locked --no-dev: шесть advisories
в http-foundation/http-kernel/routing 8.1.10. Patch update с minimal-changes
обновил только эти пакеты до 8.1.11. Полный audit выявил ещё filesystem
8.1.9 в dev graph, обновлён до 8.1.11. Composer.json и CI audit policy неизменны.

- composer validate --strict: PASS.
- composer check-platform-reqs: PASS, фактический локальный PHP 8.5.10.
- composer audit --locked --no-dev: PASS, advisories/abandoned пусты, exit 0.
- Полный audit: advisories пусты; exit 1 из-за прежнего abandoned dev package
  symplify/rule-doc-generator-contracts, без suppressions.
- composer install --dry-run --no-interaction --no-scripts: PASS, изменений нет.
- CS / STAN / Rector dry-run: PASS.

Источник исправления forwarded headers: https://github.com/symfony/symfony/security/advisories/GHSA-wg5q-m2ww-f3c9 .

- Полный PHP suite: PASS, 725 tests / 5523 assertions, 15 прежних PHPUnit notices.
  Включены HTTP startup, маршруты/API и существующие N+1 regressions.
- Frontend CI: PASS, lint/Prettier, typecheck, 85 files / 283 tests, production build.
- git diff --check: PASS. Root constraints, application code и workflow неизменны.
- Spec/contract/checklist сверены; T019–T020 выполнены, обновлена одна существующая
  запись 052 в updates. Commit/push и повторный GitHub Actions run не выполнялись.
