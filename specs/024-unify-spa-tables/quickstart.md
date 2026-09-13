# Быстрая проверка

1. Запустить unit/component тесты общего listing layout и затронутых страниц:

   ```bash
   npm run test -- --run resources/spa/components resources/spa/pages/persons
   ```

2. Запустить API и Application тесты пересчёта:

   ```bash
   vendor/bin/phpunit tests/Application/Service/Rank/RebuildPersonRanksServiceTest.php tests/Feature/Api/V1/Person
   ```

3. В конце фичи выполнить общие гейты:

   ```bash
   npm run lint && npm run typecheck && npm run test && npm run build:spa
   composer stan
   composer cs
   composer test
   ```
