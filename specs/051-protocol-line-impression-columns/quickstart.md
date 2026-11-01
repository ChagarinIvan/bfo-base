# Quickstart validation

## Prerequisites

Установленные vendor/node_modules, PHP 8.5, Node 22+, доступная MySQL test database
bfo_base_test. Не использовать рабочую базу для RefreshDatabase.

## Узкие проверки

```sh
DB_DATABASE=bfo_base_test php vendor/bin/phpunit tests/Feature/Api/V1/ProtocolLine/ListProtocolLinesActionTest.php tests/Feature/Api/V1/Cup/ListCupEventPointsActionTest.php
npm run test -- resources/spa/pages/protocolLineImpressions.test.ts resources/spa/pages/events/EventViewPage.test.ts resources/spa/pages/persons/PersonViewPage.test.ts resources/spa/pages/cups/CupEventViewPage.test.ts resources/spa/components/ImpressionDetails.test.ts resources/spa/components/ListingTable.test.ts
```

Проверить время/автора из строки, разные created/updated, guest omission, default
hidden, независимое включение, сохранение после remount/фильтра/пагинации,
старые preferences, перенос cup key, logout, пустые результаты и users failure.

## Итоговые гейты

```sh
composer cs
composer stan
composer rector -- --dry-run
DB_DATABASE=bfo_base_test composer test
npm run ci
php artisan serve --host=127.0.0.1 --port=8000
npm run dev:spa -- --host 127.0.0.1
```

Проверить HTTP /app/events/1157 и ассеты через Vite :5173, read endpoint через
PHP :8000, не меняя production. PHP serve обслуживает API, production SPA fallback
находится в Nginx. Если локального события 1157 нет, использовать доступное событие
либо fixture-проверки API.
N+1: сравнить число SQL при одной и 50 строках authenticated list, убедиться,
что users загружаются списком, а не по строке. Результаты записать validation.md.

## Ручной сценарий

Авторизованно открыть событие с результатами, включить обе колонки. Проверить
дату/автора и подробности, выключить одну, обновить страницу. Повторить на
спортсмене и этапе кубка. Очистить ключ таблицы и убедиться в hidden defaults.
Выйти, проверить отсутствие обоих пунктов и служебных данных. Проверить тёмную
тему и узкую ширину с существующим поведением прокрутки таблицы.
