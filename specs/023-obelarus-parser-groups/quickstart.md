# Быстрая проверка

1. Запустить только OBelarus.net regression:

   ```bash
   vendor/bin/phpunit tests/Models/Parser/OBelarusNetParserTest.php
   ```

2. Убедиться, что fixture `2026/20260911.htm` проходит и проверяет обычного участника
   М21, два результата других групп и снятого участника без времени и места.

3. Запустить набор parser-тестов:

   ```bash
   vendor/bin/phpunit tests/Models/Parser
   ```

4. Проверить статические гейты:

   ```bash
   composer stan
   composer cs
   ```
