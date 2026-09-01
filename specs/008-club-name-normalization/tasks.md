---

description: "Задачи для единой нормализации названий клубов"
---

# Задачи: Единая нормализация названий клубов

**Вход**: design-документы из specs/008-club-name-normalization/  
**Предусловия**: plan.md, spec.md, research.md, data-model.md, quickstart.md  
**Тесты**: обязательны: фича меняет нормализацию, persistence lookup и parser pipeline.  
**Организация**: задачи сгруппированы по пользовательским историям; Domain/Application
тесты используют моки, записи БД допустимы только в integration/API-тестах.

## Формат: [ID] [P?] [Story] Описание

- **[P]**: задачу можно выполнять параллельно с задачами без пересекающихся файлов.
- **[US#]**: пользовательская история, к которой относится задача.

## Фаза 1: Подготовка

**Цель**: зафиксировать целевой контракт и регрессионные примеры до рефакторинга.

- [X] T001 [P] Создать unit-тест единого нормализатора с регистром, внешними/повторными пробелами, кавычками, аналогами символов и EDIT_MAP в tests/Domain/Club/ClubNameNormalizerTest.php
- [X] T002 [P] Создать integration-тест поиска активного клуба по уже нормализованному имени в tests/Infrastructure/Laravel/Eloquent/Club/EloquentClubRepositoryTest.php

---

## Фаза 2: Общий фундамент

**Цель**: сделать доменные правила и семантический lookup доступными всем сценариям.

**⚠️ Критично**: завершить фазу до задач пользовательских историй.

- [X] T003 Перенести app/Domain/Club/Factory/ClubNameNormalizer.php в app/Domain/Club/ClubNameNormalizer.php и обновить его imports/uses в app/Application/Service/Club/AddClub.php, app/Application/Service/Club/AddClubService.php, app/Application/Service/Club/UpdateClubInfo.php, app/Application/Service/Club/UpdateClubInfoService.php, tests/Application/Service/Club/AddClubServiceTest.php и tests/Application/Service/Club/UpdateClubInfoServiceTest.php
- [X] T004 Добавить семантический поиск по уже нормализованному имени в app/Domain/Club/ClubRepository.php и app/Infrastructure/Laravel/Eloquent/Club/EloquentClubRepository.php, сохранив фильтрацию только активных клубов

**Контрольная точка**: один normalizer и repository lookup существуют, узкие тесты T001–T002 проходят.

---

## Фаза 3: Пользовательская история 1 — Непротиворечивые клубы (Приоритет: P1) 🎯 MVP

**Цель**: создание и редактирование продолжают отвергать дубликаты по единому правилу имени.

**Независимая проверка**: создать и переименовать клуб с эквивалентными вариантами
названия; дубликат отклоняется, уникальное значение сохраняется.

### Тесты для пользовательской истории 1

- [X] T005 [P] [US1] Обновить mock-ожидания semantic lookup в tests/Domain/Club/Factory/PreventDuplicateClubFactoryTest.php и tests/Domain/Club/ClubUpdaterTest.php
- [X] T006 [P] [US1] Добавить API-регрессии для эквивалентных вариантов названия при создании и обновлении в tests/Feature/Api/V1/Club/CreateClubActionTest.php и tests/Feature/Api/V1/Club/UpdateClubActionTest.php

### Реализация пользовательской истории 1

- [X] T007 [US1] Перевести duplicate check на semantic repository lookup в app/Domain/Club/Factory/PreventDuplicateClubFactory.php и app/Domain/Club/PreventDuplicateClubUpdater.php

**Контрольная точка**: US1 полностью проверяема без сценариев протоколов и импорта.

---

## Фаза 4: Пользовательская история 2 — Корректное сопоставление в старых протоколах (Приоритет: P1)

**Цель**: parser pipeline очищает новые данные, а legacy-рендер сопоставляет и исторические строки с внешними пробелами.

**Независимая проверка**: разобрать fixture с внешними пробелами в club и открыть
legacy-протокол с таким историческим значением; в обоих случаях сопоставляется тот же клуб.

### Тесты для пользовательской истории 2

- [X] T008 [P] [US2] Создать регрессионный тест central parser pipeline для trim поля club в tests/Services/ParserServiceTest.php
- [X] T009 [P] [US2] Создать request-тест сопоставления клуба с внешними пробелами при legacy-рендере в tests/Bridge/Laravel/Http/Controllers/Event/ShowEventActionTest.php

### Реализация пользовательской истории 2

- [X] T010 [US2] Централизованно очищать поле club в результатах protocol parser в app/Services/ParserService.php
- [X] T011 [US2] Заменить static normalizer удаляемого Finder на ClubNameNormalizer в app/Bridge/Laravel/Http/Controllers/Event/RendersEventDistance.php и передать зависимость через app/Bridge/Laravel/Http/Controllers/Event/ShowEventAction.php и app/Bridge/Laravel/Http/Controllers/Event/ShowEventDistanceAction.php

**Контрольная точка**: US2 сохраняет ссылки legacy-протокола и не создаёт N+1.

---

## Фаза 5: Пользовательская история 3 — Поддерживаемые правила названий (Приоритет: P2)

**Цель**: Finder и дублирующие правила удалены; импорт и идентификация используют общий normalizer и repository.

**Независимая проверка**: выполнить импорт, идентификацию и legacy-извлечение персоны
на варианте названия клуба; при совпадении устанавливается прежний club_id, а поиск
по кодовой базе не находит Finder-классы и их binding.

### Тесты для пользовательской истории 3

- [X] T012 [P] [US3] Добавить unit-регрессию извлечения персоны из protocol line через общий normalizer и mock ClubRepository в tests/Services/PersonsServiceTest.php

### Реализация пользовательской истории 3

- [X] T013 [US3] Перевести lookup клуба в app/Services/PersonsService.php, app/Bridge/Laravel/Console/Commands/IdentProtocolLineCommand.php и app/Infrastructure/Integration/OrientBy/OrientBySyncService.php на ClubNameNormalizer и ClubRepository
- [X] T014 [US3] Удалить app/Domain/Club/ClubFinder.php, app/Domain/Club/NormalizedNameClubFinder.php и tests/Domain/Club/NormalizedNameClubFinderTest.php; очистить Finder binding/imports в app/Bridge/Laravel/Provider/Club/ClubProvider.php и проверить отсутствие usages

**Контрольная точка**: все runtime-потребители используют один normalizer, а Finder отсутствует.

---

## Фаза 6: Завершение и сквозная проверка

**Цель**: подтвердить отсутствие регрессий, N+1 и расхождений со спецификацией.

- [X] T015 Выполнить сценарии из specs/008-club-name-normalization/quickstart.md, проверить git diff и отсутствие ClubFinder/NormalizedNameClubFinder через rg
- [X] T016 Выполнить финальные quality gates из composer.json, phpstan.neon, rector.php и phpunit.xml: composer cs, composer stan, composer rector -- --dry-run и composer test

---

## Зависимости и порядок выполнения

### Зависимости фаз

- Фаза 1 не зависит от других задач.
- Фаза 2 зависит от T001–T002 и блокирует пользовательские истории.
- US1 зависит от T003–T004.
- US2 зависит от T003; T011 также использует normalizer из T003.
- US3 зависит от T003–T004; T014 выполняется только после T011 и T013.
- Финальная фаза зависит от T007, T010–T011 и T013–T014.

### Зависимости пользовательских историй

- **US1 (P1)**: после общего фундамента; независима от протоколов и импорта.
- **US2 (P1)**: после T003; независима от duplicate rule, но T014 ждёт её завершения.
- **US3 (P2)**: после общего фундамента и после T011, поскольку Finder удаляется только после перевода legacy-рендера.

### Параллельные возможности

- T001 и T002 выполняются параллельно.
- T005 и T006 выполняются параллельно.
- T008 и T009 выполняются параллельно.
- После общего фундамента US1 и подготовка тестов US2 могут идти параллельно.
- T012 может идти параллельно с задачами US2.

## Параллельный пример: пользовательская история 2

~~~text
Task: T008 — tests/Services/ParserServiceTest.php
Task: T009 — tests/Bridge/Laravel/Http/Controllers/Event/ShowEventActionTest.php

После реализации T010:
Task: T011 — RendersEventDistance.php и два Event action
~~~

## Стратегия реализации

### MVP сначала (US1)

1. Выполнить T001–T004.
2. Выполнить T005–T007.
3. Запустить только узкие тесты US1 и проверить duplicate rule.

### Инкрементальная поставка

1. Общий фундамент делает правила имени едиными.
2. US1 сохраняет целостность справочника.
3. US2 сохраняет корректность новых и исторических протоколов.
4. US3 удаляет лишнюю абстракцию после перевода всех её consumers.
5. В конце один раз запускаются полные quality gates.

## Примечания

- Каждая задача соответствует checklist-формату, содержит точные пути и исполнима без дополнительного контекста.
- [P] означает отсутствие пересечения файлов и зависимостей с соседней задачей.
- В Application/Domain unit-тестах не создавать Eloquent-модели или записи БД; для persistence использовать integration/API-тесты.
