# Задачи: отложенная активация младших разрядов

**Input**: [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), [contracts/activation.md](contracts/activation.md)

## Phase 1: Основа

- [X] T001 Добавить миграцию `protocol_lines.delayed_rank_activation` и `person.next_rank_activation_on` с безопасными значениями по умолчанию и индексом в `database/migrations/`.
- [X] T002 Проверить Domain правило пяти разрядов в `tests/Domain/Rank/RankTest.php`, а расчёт до и после срока, конец месяца, старые факты, ручную дату и вычисленный IIIю в интеграционном `tests/Feature/Rank/DelayedRankActivationTest.php`.

## Phase 2: User Story 1 — срок одного месяца (P1)

**Goal**: новые II, III, Iю, IIю и IIIю ждут месяц, затем действуют автоматически.  
**Independent Test**: импорт нового протокола и пересчёт спортсмена до и после срока.

- [X] T003 [US1] Добавить правило пяти разрядов в `app/Domain/Rank/Rank.php` и признак нового срока в `app/Domain/ProtocolLine/ProtocolLineInput.php`, `app/Domain/Event/StandardProtocolParser.php`, `app/Domain/ProtocolLine/Factory/StandardProtocolLinesFactory.php`.
- [X] T004 [US1] Передать признак в `RankFact` через `EloquentRankFactsCollector`; рассчитать дату и ближайший срок в `RankCalculator`/`PersonRankState`, сохранить проекцию в `Person`.
- [X] T005 [US1] Расширить индексированный отбор в `EloquentPersonRepository` и существующую ежедневную команду; проверить повторный и пропущенный запуск в `tests/Feature/Rank/`.
- [X] T006 [US1] Проверить парсер, сохранение нового признака фабрикой и пересчёт всех пяти разрядов, включая явный IIIю и старый протокол с датой соревнования в прошлом.

## Phase 3: User Story 2 — ручная дата (P1)

**Goal**: оператор активирует раньше месяца, но не раньше выполнения.  
**Independent Test**: POST/PUT с датой соревнования, более поздней и более ранней датой, а также PUT null.

- [X] T007 [US2] Проверять ручную дату в `ProtocolLine::activateRank()` до изменения состояния и события, преобразовывать доменное исключение в HTTP 422 в `ActivatePersonRankService` и `UpdatePersonRankActivationDateService`; сохранить существующий transaction/event путь.
- [X] T008 [US2] Добавить API request проверки для обоих маршрутов в `tests/Feature/Api/V1/PersonRankHistory/` и регрессию ручного приоритета/отмены в `tests/Feature/Rank/`.

## Phase 4: User Story 3 — история и вычисленный IIIю (P1)

**Goal**: старые даты сохраняются, вычисленный IIIю немедленный.  
**Independent Test**: пересчёт старой строки и новых трёх стартов рядом с явным IIIю.

- [X] T009 [US3] Проверить старые активированные строки, старые строки с очищенной датой, синтетический IIIю и отличие явного IIIю в `tests/Feature/Rank/DelayedRankActivationTest.php`.
- [X] T010 [US3] Проверить повторное выполнение и сохранение более высокого действующего разряда в узких тестах; подтвердить путь замены протокола существующим `ReplaceEventProtocolTest`.

## Phase 5: Завершение

- [X] T011 Добавить запись в `resources/spa/pages/updates/updates.ts`, проверить порядок и `updates.test.ts`; сверить `quickstart.md` и все acceptance scenarios.
- [X] T012 Выполнить итоговые `composer test`, `composer stan`, `composer cs`, `composer rector -- --dry-run`, frontend CI и запуск приложения; проверить отсутствие новых N+1; отметить задачи и пункт 5 `plan.txt` после полной приёмки.

## Dependencies

### Исправление 2026-10-18

- [X] T013 Воспроизвести `null` в признаке отложенной активации на границе Eloquent и `RankFact`; трактовать его как историческое правило `false`, сохранив дату активации. Проверка: `DelayedRankActivationTest::a_legacy_null_delay_policy_keeps_the_existing_activation_date`.
- [X] T014 Проверить реальный `Results_sunday_cup.xlsx` через фабрику парсеров с MIME XLSX: `ElkPathXlsxParser`, 138 строк. Изменение формата парсера не требуется.

T001 → T002 → T003–T006 → T007–T008 → T009–T010 → T011–T012. Тесты конкретного поведения пишутся до реализации соответствующего этапа. В рамках одной фичи выполняются все три пользовательских сценария.

## Implementation Strategy

Сначала защитить старое поведение и месячную границу тестами. Затем провести признак от импорта до калькулятора и ежедневного пересчёта. После этого проверить оба ручных API и исключение IIIю. Полные проверки выполняются один раз в конце.
