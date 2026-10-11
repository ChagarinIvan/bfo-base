# Tasks: Параметры дистанции на странице этапа

**Input**: [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), [contracts/event-distance-details.md](contracts/event-distance-details.md)

**Organization**: Одна самостоятельная пользовательская история. Тест изменения поведения обязателен по конституции проекта.

## Phase 1: Setup

- [X] T001 Сверить поля длины и КП, выбор дистанции и текущие подписи в `resources/spa/api/types.ts`, `resources/spa/pages/events/EventViewPage.vue` и `resources/lang/by.json`.

## Phase 2: Foundational

Дополнительной инфраструктуры нет: страница уже получает дистанции вместе с `length` и `points`.

## Phase 3: User Story 1 - Видеть параметры выбранной группы (Priority: P1)

**Goal**: Посетитель видит длину и КП текущей группы, включая смену выбора и прямую ссылку.

**Independent Test**: Две группы с разными параметрами, выбор по умолчанию и вручную, прямая ссылка, пустые и нулевые значения.

- [X] T002 [US1] Добавить регрессионные тесты первоначального выбора, переключения группы, прямой ссылки, нулевых значений и отсутствия дистанций в `resources/spa/pages/events/EventViewPage.test.ts`; убедиться, что тесты нового поведения сначала не проходят.
- [X] T003 [US1] Вычислять выбранную дистанцию и форматировать её длину из метров в километры с белорусской десятичной запятой в `resources/spa/pages/events/EventViewPage.vue`.
- [X] T004 [US1] Вывести длину и число КП рядом с выбором группы, сохранив существующие пустое состояние и обработку протокола в `resources/spa/pages/events/EventViewPage.vue`.

## Final Phase: Validation

- [X] T005 Запустить узкий тест `resources/spa/pages/events/EventViewPage.test.ts`, TypeScript и проверки оформления изменённого SPA-кода; сверить [quickstart.md](quickstart.md) и [contracts/event-distance-details.md](contracts/event-distance-details.md).
- [X] T006 Выполнить финальные гейты `composer test`, `npm run ci`, `composer stan`, `composer cs`, Rector dry-run, проверку запуска приложения и отсутствия новых запросов/N+1; зафиксировать результаты в `specs/044-event-distance-details/tasks.md`.
- [X] T007 Отметить пункт 3 в `plan.txt`, обновить статус `specs/044-event-distance-details/spec.md` и убедиться, что `git diff --check` проходит.

## Dependencies & Execution Order

T001 предшествует T002. T002 предшествует T003 и T004. T005 выполняется после реализации, T006 после узкой проверки, T007 после финальных гейтов. US1 завершена после T004 и узкого теста.

## Parallel Opportunities

Нет независимых изменений файлов: тест и реализация требуют последовательного цикла. Финальные проверки после T005 можно запускать одновременно, если они не меняют общие файлы.

## Implementation Strategy

MVP совпадает с US1: показать параметры выбранной дистанции в существующей странице, используя загруженные данные. Не добавлять запросов и серверного кода.

## Validation

- Узкий тест страницы: 15/15; TypeScript, ESLint и Prettier изменённых файлов прошли.
- SPA CI: 84 файла, 253 теста, lint, typecheck и production build прошли. Каталог обновлений дополнен записью для этой спецификации; его узкий тест прошёл 4/4.
- `DB_DATABASE=bfo_base_test composer test`: 621 тест, 4569 assertions, 15 PHPUnit notices, без провалов.
- `composer stan`, `composer cs`, `composer rector -- --dry-run`: прошли.
- `php artisan about --only=environment` и запуск `php artisan serve`: прошли. Путь `/app/events/{id}` обслуживается Nginx по `enviroment/nginx/conf.d/app.conf.example`; `artisan serve` на него отвечает 404.
- Новый вывод использует уже загруженный список дистанций. Тест смены группы проверяет один вызов `getEventDistances`, новых серверных запросов и N+1 нет.
- `git diff --check`: прошёл.
