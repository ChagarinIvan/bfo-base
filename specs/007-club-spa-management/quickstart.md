# Quickstart: проверка SPA-управления клубами

**Фича**: `007-club-spa-management`

## Предварительные условия

1. Установить locked dependencies: `composer install` и `npm ci`.
2. Настроить MySQL и выполнить `php artisan migrate`.
3. Создать пользователя, несколько active clubs и active/inactive persons. Нужны клуб без персонов,
   клуб с несколькими страницами персонов и duplicate normalized club names для negative checks.
4. Запустить backend и SPA: `php artisan serve` и `npm run dev:spa` либо `npm run build:spa`.

Контракты:

- [Clubs V1](contracts/api-clubs.md)
- [Persons V1](contracts/api-persons.md)
- [SPA routes и redirects](contracts/ui-navigation.md)

## Сценарий 1: публичный список и filter

1. Открыть `/app/clubs` без входа.
2. Проверить default page и переходы 10/20/50.
3. Ввести один-два символа, затем три символа разным регистром и с внешними пробелами.
4. Изменить filter на поздней странице и быстро ввести два разных валидных значения.

Ожидается: короткий filter не отправляется и показывает hint; валидный применяется через ~300 ms;
page сбрасывается; остаётся результат последнего запроса; clubs идут name/id; impressions
отсутствуют.

API-проверка:

```bash
curl -i 'http://localhost:8000/api/v1/clubs?name=%20%D0%90%D1%80%D1%8B%20&page=1&perPage=20'
curl -i 'http://localhost:8000/api/v1/clubs?name=ab'
```

Ожидается 200 + pagination headers для первого запроса и 422 `field=name` для второго.

## Сценарий 2: club details и persons

1. Открыть club из списка и пройти несколько страниц persons.
2. Проверить порядок lastname/firstname/id, nullable birth year, inactive exclusion и совпадение
   `personsCount` с общим числом выдачи.
3. Открыть club без persons и missing/inactive id.
4. Перейти по person name.

Ожидается: явные empty/not-found states; person link открывает `/persons/{id}/show`; public JSON не
содержит полную birthday, rich fields и impressions.

```bash
curl -i 'http://localhost:8000/api/v1/persons?clubId=42&page=1&perPage=20'
curl -i 'http://localhost:8000/api/v1/persons?club_id=42'
```

Ожидается direct compact array для первого запроса и 422 `field=clubId` для snake_case.

## Сценарий 3: authenticated create/edit и impressions

1. Войти через `/app/login` и открыть `/app/clubs/create`.
2. Создать unique club и проверить переход на `/app/clubs/{id}`.
3. Отредактировать name; затем отправить пустое, >255 и normalized duplicate name.
4. Повторить list/detail/person requests с Bearer.

Ожидается: create 201, update 200; ошибки показаны под name без потери ввода; собственное
неизменённое normalized name допустимо; authenticated responses содержат `created`/`updated`, UI
показывает даты с календарной частью `YYYY-MM-DD`.

## Сценарий 4: authorization

1. Без токена вызвать POST/PUT и открыть guarded SPA routes.
2. Истечь/удалить токен во время submit.

Ожидается: API 401 без изменения данных; router ведёт на login с return URL; форма не показывает
защищённый контент посетителю.

## Сценарий 5: ссылки, redirects и legacy compatibility

1. Из Blade navbar открыть Clubs и Competitions.
2. Открыть club link из events, cups и legacy persons table.
3. Запросить `/clubs`, `/clubs/create`, `/clubs/42/show` и `POST /clubs/store`.
4. Проверить legacy person detail, `/api/person`, `/api/persons`, person forms, event/cup pages и
   console commands, использующие полный DTO.

Ожидается: navbar и club links ведут прямо в SPA; старые GET дают 301 на соответствующие SPA URL;
старый POST не существует; сохраняемые legacy paths продолжают работать после `Legacy*` rename.

## Сценарий 6: cleanup и query audit

Поиск не должен находить ссылки на удалённые club Blade actions/views и старый POST, кроме
contracts/tests редиректов. Должен оставаться только фактически используемый club-link partial.

Для одной и нескольких строк сравнить SQL log/listener: club list и person list не добавляют
запрос на строку; обе выдачи имеют count + bounded page query.

## Автоматизированная проверка

Во время реализации:

```bash
vendor/bin/phpunit tests/Feature/Api/V1/Club tests/Feature/Api/V1/Person
vendor/bin/phpunit tests/Application/Service/Club tests/Application/Service/Person
npm run ci
composer cs
composer stan
```

Перед завершением:

```bash
composer rector -- --dry-run
composer test
git diff --check
```

Ожидается exit code 0. Request tests покрывают public/auth projections, pagination headers,
filter/validation, stable ordering, active-only counts, create/update/not-found и отсутствие N+1;
frontend tests — routes/guards, debounce, field errors, forms, details, links и stale responses.

