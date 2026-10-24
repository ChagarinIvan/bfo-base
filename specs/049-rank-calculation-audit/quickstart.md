# Проверка 049

1. Применить миграции локального тестового окружения.
2. Узкие backend проверки: `DB_DATABASE=bfo_base_test php vendor/bin/phpunit tests/Feature/Rank tests/Feature/ProtocolLine tests/Feature/Api/V1/PersonRankAudit tests/Domain/Person`.
3. Проверить КМС после подтверждённого МС и повторный КМС после истечения срока; текущий высший разряд не понижается раньше времени.
4. Проверить будущую активацию, последний день срока и разные порядки выполнения/активации.
5. Авторизованно открыть журнал: trigger, created и два состояния; изменить разряд и сравнить даты/историю. Повторный пересчёт без изменений запись не добавляет. Анонимный запрос запрещён.
6. Проверить импорт с автором, отличным от создателя Event: created/updated строки равны Impression импорта, дальнейшее назначение/активация created не меняют.
7. `php artisan persons:ranks:correct USER_ID --personId=PERSON_ID`: preview без записи; `--apply` сохраняет показанное изменение, повтор ничего не меняет.
8. Финальные гейты: composer test, composer stan, composer cs, Rector dry-run, npm run ci, git diff --check. Приложение запускается через php artisan serve.
