# Validation

## Regression-first evidence

Все проверки выполнялись локальным PHP 8.5.10, без Docker и production доступа.
Тестовая БД bfo_base_test. Используются синтетические записи, без Sentry payload/PII.

До изменения рабочего кода:

- ProductionPhpResponseTest воспроизвёл HTTP 200 с PHP Request Startup warning
  и двумя вложенными fatal sendHeaders вместо HTTP 404.
- StandardProtocolLinesFactoryTest для 50 существующих distinct groups получил
  50 groups SELECT вместо одного. Один group и пустой ввод сохраняли прежнее поведение.
- Повторное использование factory вернуло distance первого Event во втором вызове.
- ProtocolLinePersonAssignedHandlerTest на второй доставке получил PersonNotFound
  из DisablePersonService. Unexpected storage failure тест уже проходил.
- В первом запуске исправлены только тестовые fixtures: случайный Group id заменён
  явным, normalized name получен общим normalizer, active int проверяется как int.
  Повторный red запуск подтвердил именно продуктовые дефекты до их исправления.

После исправления: 26 tests, 208 assertions, PASS. Включает новые регрессии,
DelayedRankActivationTest, UniteEventsTest, ParseEventProtocolHandlerTest и прямой
not-found DisablePersonServiceTest. Дополнительная регрессия двух Events с одной
активной Group включена в итоговый полный прогон.

## Design and scope review

- Query count считается после fixtures, только SELECT from groups.
- Существующие transaction/locks, fields, audit и domain events сохранены.
- Catch ограничен PersonNotFound при cleanup, API not-found и storage failures видимы.
- Production ini уже подключён Dockerfile COPY; новый Dockerfile diff не требуется.
- Никаких Sentry ignore/resolve операций, новых зависимостей или миграций.
- Historical schema и DI проверены текущим кодом и тестами; старый deploy недоступен.
- Результаты не доказывают, что исправления уже выложены на сервер.

## Final gates

| Gate | Result |
| --- | --- |
| composer cs | PASS, 0 files need fixing |
| composer stan | PASS, no errors |
| composer rector -- --dry-run | PASS, no changes |
| composer test, bfo_base_test | PASS, 707 tests, 5404 assertions, 15 existing PHPUnit notices |
| npm run ci | PASS, ESLint/Prettier/vue-tsc, 85 files/283 tests, Vite build |
| HTTP application startup | PASS, настоящий public/index.php, malformed POST даёт 404 без PHP output, warning в stderr |
| git diff --check | PASS |

Полный PHP и frontend наборы выполнены по одному разу после реализации.
Rector применил новое имя PHPUnit assertion только в новом handler test.
PHP CS Fixer упорядочил imports двух test files. Существующие notices не изменены.
Frontend build имеет прежнее предупреждение о chunk size, завершился успешно.

Повторный Sentry snapshot 2026-11-01 17:01:00 UTC содержит те же 9 issues,
новых записей нет. Два временных диагностических PHP-сервера остановлены;
subprocess HTTP regression завершает свой сервер через finally.


## Convergence

Converge выполнен после implement, в read-only режиме, без git comparison.
Проверены 8 FR, 4 SC, 10 acceptance scenarios трёх stories, конкретные сценарии
всех 9 issues и решения плана. Constitution principles I-IX проверены в применимой
области, 0 missing/partial/contradicts/unrequested findings. Новых задач нет;
converge не менял tasks.md. После завершения converge implement отметил T018.

18/18 tasks завершены. Production observation остаётся явно внешней проверкой,
а не незавершённой локальной реализацией. См. issue-inventory.md и quickstart.md.
Extension hooks отсутствуют. Данные доступа и персональные payload не добавлены.
