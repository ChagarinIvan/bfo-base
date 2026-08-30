# BFO Base

Laravel API и Vue SPA для платформы спортивного ориентирования.

## Требования

- PHP 8.5+
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

Команду `composer update laravel/sanctum --with-all-dependencies` достаточно выполнить
один раз после добавления Sanctum; она синхронизирует `composer.lock`.

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
