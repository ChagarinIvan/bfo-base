# Tasks: Колонки аудита строк протокола

**Input**: spec.md, plan.md, research.md, data-model.md, contracts/read-and-columns.md.
**Tests**: обязательны по конституции; сначала проверки изменённого поведения.

## Phase 1: Setup

- [x] T001 Проверить рабочее дерево, требования и review spec в specs/051-protocol-line-impression-columns/spec.md; сохранить пользовательские изменения.
- [x] T002 Подготовить и проверить дизайн в specs/051-protocol-line-impression-columns/plan.md и остальных design artifacts.

## Phase 2: Foundation

- [x] T003 Добавить проверки authenticated/guest и собственного аудита в tests/Feature/Api/V1/ProtocolLine/ListProtocolLinesActionTest.php и tests/Feature/Api/V1/Cup/ListCupEventPointsActionTest.php, FR-001/002/007/008/012.
- [x] T004 Расширить app/Application/Dto/ProtocolLine/{ViewProtocolLineDto,ProtocolLineAssembler}.php и app/Application/Dto/CupEvent/ViewCupEventPointDto.php, app/Application/Dto/Cup/CupAssembler.php приватными impressions; обновить constructor collaborators в tests/Application/Service/{Person,ProtocolLine}, FR-001/002/007/008.
- [x] T005 Добавить optional impressions в ProtocolLine и CupEventPoint в resources/spa/api/types.ts, FR-001/002/010.

## Phase 3: US1, событие

Independent test: выбрать колонки события, сверить строку и сохранить выбор.

- [x] T006 [US1] Добавить UI tests default hidden, независимого включения, remount и отказа users в resources/spa/pages/protocolLineImpressions.test.ts и resources/spa/pages/events/EventViewPage.test.ts, FR-003/005/006/009/010/011.
- [x] T007 [US1] Добавить приватные колонки, ImpressionDetails и best-effort users в resources/spa/pages/events/EventViewPage.vue, FR-001/003/005/006/009/010/011.

## Phase 4: US2, спортсмен и этап кубка

Independent test: включить аудит обеих таблиц, сверить собственный аудит строк.

- [x] T008 [US2] Добавить UI tests для аудит-колонок, guest, users failure и сохранения выбора в resources/spa/pages/protocolLineImpressions.test.ts, FR-002/003/005/006/009/010/011.
- [x] T009 [US2] Добавить колонки, users и ImpressionDetails в resources/spa/pages/persons/PersonViewPage.vue, FR-002/003/005/006/010/011.
- [x] T010 [US2] Добавить слоты аудита и best-effort users в resources/spa/pages/cups/CupEventViewPage.vue, FR-002/003/005/006/010/011.

## Phase 5: US3, настройки и доступ

Independent test: старый набор не включает аудит, auth/guest раздельны, logout
скрывает значения; очистка сохранённого ключа возвращает defaults.

- [x] T011 [US3] Покрыть старые preferences, reset через очистку key, logout и независимость таблиц в параметризованных проверках трёх страниц resources/spa/pages/protocolLineImpressions.test.ts, FR-003/004/005/007.
- [x] T012 [US3] Передать authenticated и сохранить прежний выбор из guest key при первом переходе к auth key в resources/spa/pages/cups/CupEventViewPage.vue, FR-004/005/007.

## Phase 6: Verification and documentation

- [x] T013 Добавить ровно одну запись в resources/spa/pages/updates/updates.ts и обновить latest spec в resources/spa/pages/updates/updates.test.ts.
- [x] T014 Выполнить итоговые гейты, N+1 и app smoke по specs/051-protocol-line-impression-columns/quickstart.md, записать validation.md, FR-008/009/012, SC-001–006.
- [x] T015 Выполнить converge со spec/plan/tasks, зафиксировать evidence и статус в specs/051-protocol-line-impression-columns/validation.md и spec.md.

## Dependencies and execution order

T001 -> T002 -> T003 -> T004 -> T005. Далее US1 -> US2 -> US3 -> T013 -> T014 -> T015.
US1 и US2 используют общую foundation, сценарии проверяются независимо.
В каждой фазе тесты пишутся до реализации. Полные гейты один раз в конце.

## Parallel examples

US1 API/UI tests затрагивают разные файлы; US2 person/cup UI tests также независимы.
US3 проверки разных страниц независимы, но auth storage миграция следует после
теста старых настроек. MySQL RefreshDatabase tests запускаются последовательно.
Данная реализация выполняется последовательно; research agent только читал код.

## Implementation strategy

Сначала общий read contract, затем MVP события, остальные две таблицы и защита
настроек. После узких проверок итоговые gates и сверка полного набора FR/SC/AC.

## Completion

Все 15 задач выполнены. Analyze и converge завершены без оставшейся работы.
Результаты и ограничения ручной проверки приведены в [validation.md](validation.md).
