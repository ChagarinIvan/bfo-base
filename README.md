# BFO Base

Laravel API и Vue SPA для платформы спортивного ориентирования.

## Требования

- PHP 8.5 для разработки, PHP 8.5.12+ с исправлениями безопасности для сервера
- Composer
- Node.js 22+ и npm 10+
- MySQL 8.4 и Redis 8

## Установка

Для текущего `package-lock.json` нужен Node.js 22 и npm 10 или новее. Если Node
уже установлен через nvm, выполните `nvm install` и `nvm use` — версия берётся из
`.nvmrc`.

```bash
cp .env.example .env
composer install
php artisan key:generate
npm ci
php artisan migrate
```

На production frontend собирается командой `npm run build:spa` до запуска
контейнеров. Если Node/npm на production не устанавливаются, выполните сборку в
CI и доставьте каталог `public/spa/` вместе с release-файлами.

Для локальной базы данных можно запустить инфраструктуру Docker:

```bash
docker compose -f docker-compose.yml.example up -d db redis
```

В `.env` при запуске PHP и Vite на хосте должны быть настройки MySQL:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bfo_base
DB_USERNAME=root
DB_PASSWORD=root_password
```

Для обновления зависимостей разработчик выполняет `composer update --with-all-dependencies`
и фиксирует проверенный `composer.lock`. Для обычной установки и выкладки используется
`composer install`. После обновления проверьте `composer validate --strict`,
`composer check-platform-reqs` и `composer audit --locked`.

## Обновление PHP на сервере

Серверный Dockerfile и CI используют PHP 8.5.12 с
[исправлениями безопасности](https://www.php.net/archive/2026.php#2026-10-21-5).
Файл `enviroment/php/99-security.ini` отключает session upload progress согласно
[дополнительной рекомендации PHP](https://www.php.net/archive/2026.php#2026-10-23-1).
Он копируется в образ и доступен при монтировании каталога `enviroment/php` через
Docker Compose. Обычная загрузка файлов сохраняется.

При выкладке пересоберите образ с обновлением базового образа, пересоздайте
контейнер приложения и установите зависимости из зафиксированного lock-файла:

```bash
docker compose build --pull app
docker compose up -d --no-deps app
docker compose exec app composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
docker compose exec app php --version
docker compose exec app php -r 'echo "session.upload_progress.enabled=", ini_get("session.upload_progress.enabled"), PHP_EOL;'
```

Ожидаются PHP 8.5.12 и значение `0`. Перезапустите Horizon и остальные queue workers
штатным механизмом сервера, чтобы они использовали новый интерпретатор.
Изменение Dockerfile не обновляет уже работающие процессы. Локальный PHP для
этого серверного обновления переустанавливать не требуется; `composer.json`
сохраняет совместимость с веткой 8.5 и не подменяет версию через `config.platform`.

## Запуск SPA в dev-режиме

Запусти backend в одном терминале:

```bash
php artisan serve
```

Запусти Vite SPA во втором:

```bash
npm run dev:spa
```

Открой <http://127.0.0.1:5173/app/competitions>.

Vite проксирует запросы `/api/*` на Laravel по адресу `http://127.0.0.1:8000`, поэтому
отдельная CORS-настройка для локальной разработки не нужна. Страница списка соревнований
открывается публично; для создания соревнования нужен Bearer-токен через страницу входа.

Можно запустить оба процесса через Make:

```bash
make install       # зависимости и базовая настройка
make dev           # Laravel + Vite параллельно
```

## Полезные команды

```bash
make backend       # php artisan serve
make frontend      # npm run dev:spa
npm run build:spa  # production-сборка в public/spa/
composer test      # PHPUnit
```
