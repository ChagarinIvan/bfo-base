# Быстрая проверка

1. Запустить только OBelarus.net regression:

   ```bash
   vendor/bin/phpunit tests/Models/Parser/OBelarusNetParserTest.php
   ```

2. Запустить набор parser-тестов:

   ```bash
   vendor/bin/phpunit tests/Models/Parser
   ```

3. Запустить статические гейты:

   ```bash
   composer stan
   composer cs
   ```
