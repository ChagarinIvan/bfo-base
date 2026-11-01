# Quickstart validation and deployment

## Уже выполнено при подготовке

```sh
composer update --with-all-dependencies --no-interaction --no-progress
composer validate --strict
composer check-platform-reqs
composer audit --locked
composer audit --locked --no-dev
composer install --no-interaction --no-progress
```

Полный audit отдельно сообщает об abandoned dev package; это не security advisory.
После install сравнить SHA-256 composer.lock. Долгие локальные suites не запускались
по уточнению пользователя. CI сохраняет существующие проверки на PHP 8.5.12.

## Серверная выкладка пользователем

```sh
docker compose build --pull app
docker compose up -d --no-deps app
docker compose exec app composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
docker compose exec app php --version
docker compose exec app php -r 'echo ini_get("session.upload_progress.enabled"), PHP_EOL;'
```

Ожидаются 8.5.12 и 0. Перезапустить Horizon и остальные queue workers штатным
механизмом сервера. Проверить read API, импорт протокола и фоновые расчёты.
Runtime security не подтверждается одним audit Composer. Локальный Docker не нужен.
