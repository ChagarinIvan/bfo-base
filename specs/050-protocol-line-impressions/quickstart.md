# Validation guide

1. Использовать локальную тестовую MySQL-базу bfo_base_test; не применять
   migrate:fresh к рабочим данным.
2. Узкая проверка: `DB_DATABASE=bfo_base_test php vendor/bin/phpunit tests/Feature/ProtocolLine`.
3. Проверить импорт и ранги: узкие EventProcessingFailureTest,
   DelayedRankActivationTest и ElkPathCupEliteGroupsTest.
4. Финал: `DB_DATABASE=bfo_base_test composer test`, `composer stan`, `composer cs`,
   `composer rector -- --dry-run`, `npm run ci`, запуск приложения и HTTP smoke.
5. Миграционные тесты должны подтвердить точный backfill, неизменность результатов,
   отсутствие events, повреждённые связи, пустую таблицу, rollback и повторный up.
6. На выкладке остановить импортные воркеры и конкурентную запись до миграции;
   после успешного применения запустить их с обновлённым кодом.
