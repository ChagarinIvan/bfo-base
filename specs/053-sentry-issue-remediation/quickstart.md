# Validation and deployment guide

## Local prerequisites

PHP 8.5, установленные composer/npm dependencies, MySQL с отдельной
bfo_base_test. Docker локально не требуется. Исходные Sentry events не загружать
в fixtures, использовать синтетические имена и malformed multipart.

## Narrow checks

```bash
DB_DATABASE=bfo_base_test php vendor/bin/phpunit tests/Feature/Infrastructure/ProductionPhpResponseTest.php tests/Feature/ProtocolLine/StandardProtocolLinesFactoryTest.php tests/Feature/ProtocolLine/ProtocolLinePersonAssignedHandlerTest.php
DB_DATABASE=bfo_base_test php vendor/bin/phpunit tests/Feature/Rank/DelayedRankActivationTest.php tests/Feature/Event/UniteEventsTest.php tests/Application/Handler/Event/ParseEventProtocolHandlerTest.php tests/Application/Service/Person/DisablePersonServiceTest.php
```

HTTP subprocess использует случайный loopback port, production ini и реальный
public/index.php. Он не обращается в production и отключает Sentry transport.
Проверяются HTTP status, отсутствие diagnostics в body и сохранение startup warning
в stderr. Factory tests считают только SELECT groups, исключая fixture setup.

## Final gates, once at completion

```bash
composer cs
composer stan
composer rector -- --dry-run
DB_DATABASE=bfo_base_test composer test
npm run ci
git diff --check
```

Ожидаются зелёные результаты. Known PHPUnit notices перечислять отдельно.

## Production actions for operator

Использовать существующий порядок compose/deploy проекта и новый immutable image.
Эти команды здесь документируются, агент на production их не выполняет.

1. Приостановить новые jobs/дождаться текущих по существующей процедуре Horizon.
2. Пересобрать app image с PHP 8.5.12 и актуальным enviroment/php/99-security.ini.
3. Установить dependencies из lock и применить миграции `php artisan migrate --force`
   до возобновления workers. `php artisan migrate:status` должен показывать applied
   для 2026_10_17_000001_add_delayed_rank_activation.
4. Проверить в новом контейнере CLI и FPM ini: display_errors=Off,
   display_startup_errors=Off, log_errors=On, session.upload_progress.enabled=Off.
   Проверить FPM pool overrides отдельно, CLI вывод не доказывает настройки FPM.
5. Обновить config cache согласно процедуре проекта, перезапустить FPM и Horizon
   `php artisan horizon:terminate`, затем убедиться, что supervisor поднял новые workers.
6. Проверить event import и reassignment, scheduled ranks rebuild; в Sentry
   проверить отсутствие повторов FC/FD/F6/FB на новой версии. F7/F8/F9/FA/F0
   сверить со схемой и новым worker. Не закрывать issues до такого наблюдения.

Прямые запросы на probe path и отключение logging не требуются. Raw requests/PII
из production не сохранять в репозитории. Локальный PASS не доказывает deploy PASS.
