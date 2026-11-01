# Validation: Колонки аудита строк протокола

Дата проверки 2026-11-01. Реализация и проверки выполнены на master.
Для фиксации по последующему запросу пользователя создана ветка
051-protocol-line-impression-columns. Полный цикл разрешён пользователем.
Фазы specify, review, plan, review, tasks, analyze, implement и converge выполнены.
Extension hooks отсутствуют. Пользовательские staged/deleted файлы не изменялись.

## Analyze перед реализацией

12 функциональных требований, 6 критериев успеха, 3 user stories, 15 задач.
Покрытие требований задачами 100%. Критических проблем, неоднозначностей,
конфликтов конституции и задач без назначения не найдено.

## Результат

Две колонки created/updated со standard ImpressionDetails доступны после входа
на событии, спортсмене и этапе кубка. Обе hidden by default, независимы, сохраняются
через ListingTable. Аудит принадлежит строке, включая таблицу баллов этапа кубка.
DTO-поля имеют authenticated groups и отсутствуют в guest JSON.
Старые настройки сохранены; прежний guest key этапа кубка переносится только при
отсутствии authenticated key и только с известными публичными колонками.
Справочник авторов best-effort; неизвестный автор не ломает результаты.
Добавлена ровно одна запись updates, latest catalog test обновлён.

## Проверки

| Проверка | Результат |
| --- | --- |
| API regression red | Два новых теста воспроизвели отсутствие аудита до реализации |
| UI regression red | 14 проверок не проходили до добавления колонок и переноса настроек |
| API narrow final | 20 tests, 220 assertions, exit 0 |
| Application narrow | 6 tests, 32 assertions, exit 0 |
| composer cs | exit 0 |
| composer stan | exit 0, No errors |
| composer rector -- --dry-run | exit 0, без предложенных изменений |
| DB_DATABASE=bfo_base_test composer test | exit 0, 698 tests, 5320 assertions |
| npm run ci | exit 0, lint/typecheck, 85 test files, 283 tests, build |
| git diff --check | exit 0 |

Полный PHP-набор сохраняет 15 PHPUnit notices, как в validation фичи 050.
Новые тесты notices не добавили. Vite сообщает существующее предупреждение о
размере bundle; сборка проходит. Нагрузочный UI-сценарий с 50 реальными строками
и 100 ячейками impressions имеет локальный timeout 20 секунд вместо глобального
лимита 5 секунд. Он проверяет число загрузок, а не время рендера.

## Coverage

Основной frontend evidence: resources/spa/pages/protocolLineImpressions.test.ts,
24 проверки с реальными ListingTable, DataTable и ImpressionDetails.
Регрессии прежних страниц также входят в полный frontend CI.

| Требования и сценарии | Evidence |
| --- | --- |
| FR-001/002, SC-001/002, US1/AC2, US2/AC1–3 | Оба API request-теста сверяют at/by из сохранённой строки; UI проверяет обе даты и email на трёх страницах |
| FR-003, US1/AC1/3, US3/AC3 | Default hidden, независимые checkbox и очистка preferences с remount на трёх страницах |
| FR-004, US3/AC2 | Старые authenticated preferences сохраняются; cup legacy переносится без перезаписи существующего auth выбора и без private/unknown keys |
| FR-005, SC-003, US1/AC4, US2/AC4/5 | UI pagination, remount и отдельные storage keys; прежние тесты фильтрации каждой страницы проходят |
| FR-006, US1/AC5 | Реальный ImpressionDetails с датой/email; существующие component tests для stale/unknown author, подробности popover сохранены без изменений |
| FR-007, SC-004, US3/AC1/4 | Bearer request tests, guest omission, UI со stale private данными и сохранёнными ключами; logout скрывает аудит, guest не вызывает users/refreshUsers |
| FR-008 | Read tests сравнивают persisted impressions после запросов; Domain и write services не изменены |
| FR-009 | Существующие page и API suites проходят, фильтры/порядок/пагинация сохранены |
| FR-010, SC-005 | Пустая выборка, missing Impression и users failure на трёх страницах; placeholder без подстановки другой сущности |
| FR-011 | Используются существующие белорусские labels, UI-тесты выбирают Створана/Зменена |
| FR-012, SC-006 | API сравнивает число SELECT на одной и 50 строках; UI 50 строк на каждой странице использует один getUsers и ноль refresh для известных авторов |

## N+1

Аудит берётся из атрибутов protocol_lines.*, без новых relations, repository
queries и SQL. CupEventPoint уже содержит свой ProtocolLine. После предварительного
разрешения Bearer auth число SELECT совпадает для 1 и 50 строк.
На каждой странице 50 строк загружают один список авторов, а не 50 отдельных.
Неизвестные авторы используют существующий refreshUsers с общим in-flight promise.

## Local app smoke и ограничения

PHP artisan serve запущен на 127.0.0.1:8000, Vite на 127.0.0.1:5173.
GET http://127.0.0.1:5173/app/events/1157 и /main.ts вернули HTTP 200.
GET http://127.0.0.1:8000/api/v1/events?year=2026&perPage=1 вернул HTTP 200
с пустой выборкой. Локального события 1157 нет, его API вернул ожидаемый
404 event_not_found. Контракт реальных результатов проверен на MySQL fixtures.
PHP artisan serve обслуживает API, SPA fallback в production обслуживает Nginx;
поэтому прямой /app/events/1157 на PHP-порту не является проверкой SPA.
Browser connector недоступен, native Computer Use permissions не выданы.
Ручная визуальная проверка не выполнялась. Рендер/выбор колонок и logout проверены
компонентными интеграционными тестами; CSS и формат impressions не менялись.
Сервисы, поднятые для smoke, остановлены после проверки.

## Converge

Проверены FR-001–012, SC-001–006, все user stories, их acceptance scenarios,
edge cases, plan decisions и требования конституции по текущему коду и тестам.
Результат converged. Не выявлено missing, partial, contradicts или unrequested
работы в заданной области. Новые convergence tasks не добавлялись.
Все 15 задач завершены. Пользователь отдельно запросил фиксацию файлов фичи
в ветке 051-protocol-line-impression-columns. Deploy не выполнялся.
