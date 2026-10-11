# Tasks: Поиск этапа при добавлении в кубок

**Input**: [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), [contracts/cup-event-options.md](contracts/cup-event-options.md)

**Organization**: Одна история пользователя. Регрессионные тесты обязательны.

## Phase 1: Setup

- [X] T001 Сверить текущую выборку, `Criteria`, заголовки `Slice` и форму создания в файлах из [plan.md](plan.md); сохранить пользовательский diff в `app/Domain/Event/Event.php`.

## Phase 2: Foundational API

- [X] T002 [US1] Сначала добавить request-тест поиска по двум названиям, ограничений года/кубка, сортировки, страниц и неверных параметров в `tests/Feature/Api/V1/Event/ListEventsActionTest.php`; проверить ожидаемое падение.
- [X] T003 [US1] Добавить в `app/Application/Dto/Event/SearchEventDto.php` проверенные параметры `competitionOrEventName`, `sortBy`, `sort` и передать сортировку в `Criteria` через `app/Application/Service/Event/ListEvents.php`.
- [X] T004 [US1] Применить объединённый поиск и устойчивую сортировку до пагинации в `app/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepository.php`.
- [X] T005 Проверить узкий API request-тест и отсутствие новых запросов на строку результата.

## Phase 3: User Story 1 - Найти и выбрать этап (Priority: P1)

**Goal**: Форма создания показывает первые 20 подходящих этапов от новых к старым и ищет по всем подходящим этапам.

**Independent Test**: Этап за пределами первой страницы найден по названию соревнования и по названию этапа, выбран и сохранён.

- [X] T006 [US1] Добавить тест параметров и заголовков нового запроса в `resources/spa/api/events.test.ts`, тесты поиска/страниц/поздних ответов/выбора в `resources/spa/pages/cups/CreateCupEventPage.test.ts` и режима формы в `resources/spa/pages/cups/CupEventForm.test.ts`; проверить ожидаемое падение.
- [X] T007 [US1] Добавить paginated клиентский вызов и тип параметров в `resources/spa/api/events.ts` и `resources/spa/api/types.ts`.
- [X] T008 [US1] Добавить отдельный режим серверного поиска, пустое состояние, подсказки и пагинатор в `resources/spa/pages/cups/CupEventForm.vue`, подписи в `resources/lang/by.json`.
- [X] T009 [US1] Загружать страницы по 20 и сбрасывать страницу при поиске в `resources/spa/pages/cups/CreateCupEventPage.vue`; не принимать устаревшие ответы и сохранять выбранный этап между страницами.
- [X] T010 Проверить узкие SPA тесты, TypeScript, ESLint и Prettier; сверить [quickstart.md](quickstart.md) и контракт.

## Final Phase: Validation

- [X] T011 Добавить запись о фиче в `resources/spa/pages/updates/updates.ts`, проверить `resources/spa/pages/updates/updates.test.ts`.
- [X] T012 Выполнить полный `composer test`, `npm run ci`, `composer stan`, `composer cs`, Rector dry-run, запуск приложения, проверку N+1 и `git diff --check`; записать результаты здесь.
- [X] T013 Обновить статус [spec.md](spec.md) после приёмки и проверить, что пользовательская правка `app/Domain/Event/Event.php` сохранена.

## Корректировка интерфейса

- [X] T014 Вернуть поиск во встроенное поле `Select`, разместить пагинацию внутри списка и не отсеивать серверные результаты на клиенте. Обновить SPA-тесты и описание интерфейса в спецификации.
- [X] T015 Убрать пагинацию из `Select`, всегда запрашивать первые 20 вариантов, оставить серверный поиск по всему набору. Удалить неиспользуемый клиентский paginated вызов и обновить тесты, спецификацию и историю обновлений. T015 заменяет решение о пагинации в T014.
- [X] T016 Показывать дату этапа в скобках у вариантов раскрытого списка создания, сохранив прежнюю подпись выбранного значения и форму редактирования. Обновить SPA-тест и спецификацию.

## Dependencies & Execution Order

T001 перед T002. T002 перед T003–T004. T005 перед SPA. T006 перед T007–T009. T010 перед финальными проверками. T011 до T012, T013 после T012. Корректировки T014–T016 выполнены последовательно после T013.

## Implementation Strategy

Сначала серверный поиск и сортировка с request-тестом, затем клиент и форма с SPA-тестами. Форма редактирования остаётся в текущем режиме.

## Validation

- API request-тест сначала обнаружил отсутствие серверного поиска и сортировки; отдельный сценарий нашёл ошибку исключения этапа при связях с двумя кубками. После исправления узкий тест прошёл: 17 тестов, 122 assertions.
- SPA-регрессии сначала обнаружили отсутствие серверного запроса и локальный фильтр. Узкие тесты клиента, создания, формы, редактирования и истории обновлений прошли: 19 тестов.
- `DB_DATABASE=bfo_base_test composer test`: 624 теста, 4592 assertions, 15 PHPUnit notices, без провалов. После уточнения типов в request-тесте его узкий прогон прошёл повторно.
- `npm run ci`: lint, typecheck, 84 файла и 257 тестов, production build прошли.
- `composer cs`, `composer rector -- --dry-run`, `php artisan about --only=environment` и запуск `php artisan serve`: прошли.
- Полный `composer stan` сообщает 12 ошибок из-за уже существовавшей пользовательской правки `app/Domain/Event/Event.php`, где PHPDoc `@property Carbon $date` заменён на `$c`. Этот файл сохранён без изменений. PHPStan уровня 5 для изменённых PHP-файлов и request-теста прошёл.
- Сервер фильтрует и сортирует до `Slice`; имя соревнования загружается через существующий eager load. Дополнительного запроса на каждый этап нет.
- `git diff --check`: прошёл.
- После T014 `npm run ci` прошёл: lint, typecheck, 84 файла и 258 SPA-тестов, production build. Встроенный фильтр `Select` проверен вместе с серверным запросом и сохранением выбора; `git diff --check` прошёл.
- После T015 `npm run ci` прошёл: lint, typecheck, 84 файла и 258 SPA-тестов, production build. Узкие тесты подтвердили запрос `page=1&perPage=20`, поиск по всем подходящим этапам и отсутствие пагинатора в `Select`; серверный код не менялся.
- После T016 `npm run ci` прошёл: lint, typecheck, 84 файла и 259 SPA-тестов, production build. Тест формы проверил дату в варианте создания и прежний вид выбранного значения и вариантов редактирования.
