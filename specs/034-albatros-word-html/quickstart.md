# Быстрая проверка

1. Запустить только Albatros-Timing regression:

   ```bash
   vendor/bin/phpunit tests/Models/Parser/AlbatrosTimingParserTest.php
   ```

2. Запустить набор parser-тестов:

   ```bash
   vendor/bin/phpunit tests/Models/Parser
   ```

3. Запустить PHP quality gates:

   ```bash
   composer stan
   composer cs
   ```

4. Проверить, что fixture возвращает 248 строк из групп М1–М5 и Ж1–Ж5, включая
   строки без результата.
