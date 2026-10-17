# Tasks: элитные группы «Лосиной сцежки»

**Input**: [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), [contracts/cup-groups.md](contracts/cup-groups.md).

## Phase 1: Setup

- [X] T001 Проверить спецификацию, исходный файл и причины несовпадения; записать решения в `specs/048-elk-path-elite-groups/research.md` и `plan.md`.

## Phase 2: Foundational

- [X] T002 Сохранить неизменённый контрольный файл в `tests/Fixtures/Protocols/2026/Results_night_cup.xlsx`; добавить основу импорта и persisted fixtures в `tests/Feature/Cup/ElkPathCupEliteGroupsTest.php`.

## Phase 3: User Story 1 (P1)

**Goal**: результаты обеих элитных групп включены в правильный зачёт.
**Independent Test**: реальный XLSX проходит импорт и расчёт, 19 мужских и 6 женских строк, без смешения и дублей.

- [X] T003 [US1] Воспроизвести красным тестом полный путь импорта/расчёта и состав обеих групп в `tests/Feature/Cup/ElkPathCupEliteGroupsTest.php` (FR-001–FR-003, FR-006–FR-007).
- [X] T004 [US1] Сохранить прежние шаблоны в `app/Domain/Cup/CupType/ElkPathCup.php` и реализовать явный критерий `groupNamesIgnoringSpaces` в `app/Infrastructure/Laravel/Eloquent/Distance/EloquentDistanceRepository.php`, выполнить узкий тест.

## Phase 4: User Story 2 (P2)

**Goal**: существующий этап работает после очистки кэша; прежние категории и очки сохранены.
**Independent Test**: прежние aliases и Short/Youth/Kids дают прежние очки; кэшированная пустая таблица заменяется результатами без переимпорта.

- [X] T005 [US2] Добавить регрессию прежних aliases, остальных категорий, отсутствующей элиты и посторонней группы в `tests/Feature/Cup/ElkPathCupEliteGroupsTest.php` (FR-005–FR-006).
- [X] T006 [US2] Проверить восстановление из уже сохранённых строк после штатной очистки кэша и отсутствие роста запросов на спортсмена в `tests/Feature/Cup/ElkPathCupEliteGroupsTest.php`; уточнить `specs/048-elk-path-elite-groups/quickstart.md` (FR-004).

## Phase 5: Завершение

- [X] T007 Добавить белорусскую запись фичи в `resources/spa/pages/updates/updates.ts`, обновить ожидание последней записи в `resources/spa/pages/updates/updates.test.ts`.
- [X] T008 Проверить acceptance scenarios и FR-001–FR-007, выполнить один раз полные backend/frontend гейты и запуск приложения; записать результаты в `specs/048-elk-path-elite-groups/quickstart.md`, отметить `tasks.md` и статус `spec.md` по факту.

- [X] T009 По уточнению от 2026-10-18 проверить поиск без обычных пробелов и пустой список в `tests/Feature/Cup/ElkPathCupEliteGroupsTest.php`, сохранить поведение обычного groupNames и обновить документы фичи.

## Dependencies & Execution Order

T001 → T002 → T003 → T004 → T005 → T006 → T007 → T008 → T009. US1 и US2 используют один тестовый файл, поэтому реализуются последовательно.

## Parallel Examples

После T004 обновление каталога T007 можно подготовить независимо от тестов US2. Для столь малого изменения реализация остаётся последовательной; независимые read-only исследования и финальные статические проверки допустимы параллельно, полные тесты БД не запускаются одновременно.

## Implementation Strategy

Сначала красная регрессия реального импорта, затем сопоставление прежних шаблонов без обычных пробелов и зелёный тест. После него совместимость, кэш и N+1, история обновлений и итоговые гейты. Обе пользовательские истории входят в выпуск. Новые зависимости и миграции не требуются.
