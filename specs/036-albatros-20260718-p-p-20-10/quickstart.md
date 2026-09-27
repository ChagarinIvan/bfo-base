# Быстрая проверка

1. Запустить focused regression:

   ```bash
   php vendor/bin/phpunit --no-progress --filter='Tests\\Models\\Parser\\AlbatrosTimingParserTest::parse' tests/Models/Parser/AlbatrosTimingParserTest.php
   ```

2. Убедиться, что строки Бильдюкевич, Атрошко, Кульгавый и Долмат имеют
   `time=null`, `place=null` и ненулевой `runner_number`.
3. Запустить весь parser suite:

   ```bash
   php vendor/bin/phpunit --no-progress tests/Models/Parser
   ```

4. Запустить `composer cs`, `composer stan` и `git diff --check`.
