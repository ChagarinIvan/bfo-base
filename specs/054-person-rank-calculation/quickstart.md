# Validation guide

Локально PHP 8.5, dependencies и отдельная MySQL bfo_base_test, без Docker.

1. Узкий набор PersonRankCalculation*, существующие rank/command/job tests.
2. При успешном manual rebuild проверить metadata в auth PersonView; guest не
   получает ключи. Повтор с неизменным rank обновляет время; ошибка откатывает его.
3. Protocol import/cleanup/reassignment -> protocol, activation -> manual,
   rebuild-expired -> daily, ranks:refill -> refill.
4. Проверить migration up/down/up на тестовой БД, rank не меняется, null/null сохраняются.
5. В конце один полный `DB_DATABASE=bfo_base_test composer test`, `composer cs`,
   `composer stan`, `composer rector -- --dry-run`, `npm run ci`, `git diff --check`.

На сервере применять `php artisan migrate --force` до возобновления workers с новым
кодом. Перезапустить Horizon по существующей процедуре. Миграция не запускает
перерасчёт. Старые metadata остаются null до очередного расчёта. Production deploy
не выполняется агентом в рамках этой задачи.
