# Быстрая проверка

1. Проверить API-контракт:

   ```bash
   php vendor/bin/phpunit --no-progress tests/Feature/Api/V1/Cup/CupTableActionTest.php
   ```

2. Проверить application mapping и scoring compatibility:

   ```bash
   php vendor/bin/phpunit --no-progress tests/Application/Service/Cup
   ```

3. Проверить SPA:

   ```bash
   npm run lint
   npm run typecheck
   npm run test -- --run
   ```

4. Проверить full gates в конце feature: `composer cs`, `composer stan`,
   `composer rector -- --dry-run`, `composer test`, `npm run build:spa`.

5. Вручную открыть `/app/cups/60`, переключить «Таблица», группы, страницы и
   фильтр имени; убедиться, что карточка не перезагружается, этапные колонки
   нельзя скрыть, а ссылки ведут на нужную строку протокола.
