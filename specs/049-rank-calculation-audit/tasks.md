# Tasks: упрощение 049 после ревью PR #175

**Input**: spec.md, plan.md, research.md, data-model.md, contracts/rank-audit.md, review.md.
**History**: выполненные T001–T028 предыдущей модели сохранены в [tasks-history.md](tasks-history.md). Здесь только работа новой редакции.

## Phase 1: Setup

- [x] T029 Пересмотреть требования и дизайн по всем 19 комментариям в specs/049-rank-calculation-audit/spec.md, review.md, plan.md и связанных артефактах; подтвердить отсутствие новых архитектурных исключений (FR-001–FR-014).

## Phase 2: Foundation

- [x] T030 Добавить тесты чистого RankAuditAction в tests/Domain/Person/Audit/RankAuditActionTest.php; заменить снимки обязательными PersonRank/nextActivationOn в app/Domain/Person/Audit/RankAuditAction.php, сохранить enum шести причин (FR-004–FR-008, FR-013).
- [x] T031 Добавить pending rankAudit в app/Domain/Person/Person.php, минимальную запись applied JSON в app/Infrastructure/Laravel/Eloquent/Person/RankAudit/RankAuditModel.php и сохранение через EloquentPersonRepository.php; удалить snapshot/failure классы; изменить миграцию и проверить integration запись/rollback в tests/Feature/Rank/RankAuditTest.php (FR-004–FR-009).

## Phase 3: US1, расчёт и точки применения

Независимая проверка: прежние regression examples проходят, ошибки откатываются без failed audit, каждая бизнес-причина приходит из инициатора.

- [x] T032 [US1] Упростить app/Domain/Person/PersonRanksUpdater.php, StandardEventPersonRankUpdater.php, app/Application/Service/Person/RebuildPersonRanksService.php и app/Application/Service/Event/CleanupEventResultsService.php; удалить catches ради аудита и внешнюю дату команды, обновить tests/Application/Service/Rank/RebuildPersonRanksServiceTest.php и интеграционные tests/Feature/Person/StandardEventPersonRankUpdaterTest.php (FR-001–FR-002, FR-004, FR-008).
- [x] T033 [US1] Передавать trigger явно из CLI/handlers/jobs через app/Application/Service/Person/RebuildExpiredPersonRanks.php; убрать Queued/Refill/Correction, обновить tests/Bridge/Laravel/Jobs/RebuildPersonRanksJobTest.php и связанные проверки (FR-001, FR-007).
- [x] T034 [US1] Инкапсулировать уникальные прежние person IDs в app/Application/Service/ProtocolLine/SetPersonToProtocolLines.php; упростить service и проверить старого/нового владельцев в tests/Application/Service/ProtocolLine/AssignPersonOnProtocolLinesServiceTest.php (FR-001, FR-004).
- [x] T035 [US1] Согласовать обязательные ProtocolLine Impression и backfill в database/migrations/2026_10_24_000001_add_rank_audit.php; заменить nullable legacy test тестом миграции в tests/Feature/ProtocolLine/ProtocolLineImpressionsTest.php или отдельном migration test; проверить сохранность created, включая копии в app/Infrastructure/Laravel/Eloquent/Event/EloquentUniteEventDataService.php и tests/Feature/Event/UniteEventsTest.php (FR-012).

## Phase 4: US2, общее подтверждение

Независимая проверка: матрица I/КМС/МС/МСМК, истёкшее основание, ручная дата и отрицательные случаи.

- [x] T036 [US2] Сохранить app/Domain/Person/RankActivationPolicy.php и расчётные исправления; перевести вызовы с on на FrozenClock в tests/Feature/Rank/RankActivationTest.php и tests/Feature/ProtocolLine/StandardProtocolLineIdentifierTest.php; выполнить узкие policy/regression тесты (FR-002–FR-003).

## Phase 5: US3, простой журнал

Независимая проверка: обязательный applied, несколько записей повторного применения, пустой список неизвестного/неактивного Person, без ошибки в аудите.

- [x] T037 [US3] Заменить dedup/failure-entry проверки в tests/Feature/Rank/RankAuditTest.php на повторные успешные применения, отсутствие дубля при обычном save, сохранность аудита и атомарный rollback (FR-004–FR-009).
- [x] T038 [US3] Упростить app/Domain/Person/Audit/RankAuditRepository.php и Infrastructure EloquentRankAuditRepository.php до indexed read-only query с active Person; ListRankAudit использует criteria property hook, ListRankAuditService не проверяет Person отдельно (FR-009–FR-010).
- [x] T039 [US3] Добавить app/Application/Dto/Person/RankAudit/AppliedRankDto.php; обновить RankAuditDto/Assembler и tests/Feature/Api/V1/PersonRankAudit/RankAuditApiTest.php под applied, пустые результаты и пагинацию (FR-005–FR-006, FR-010, FR-013–FR-014).
- [x] T040 [P] [US3] Упростить resources/spa/api/personRankAudit.ts и resources/spa/pages/persons/PersonRankAudit.vue; обновить UI tests и resources/lang/by.json, сохранив загрузку, retry, пагинацию и защиту от устаревших ответов (FR-005–FR-006, FR-010, FR-014).

## Phase 6: US4, полный пересчёт

Независимая проверка: refill восстанавливает испорченные производные данные, сохраняет источники и аудит; повтор спортивно одинаков и добавляет успешные применения.

- [x] T041 [US4] Удалить app/Application/Service/Person/CorrectPersonRanks*.php, app/Bridge/Laravel/Console/Commands/CorrectPersonRanksCommand.php и прежний tests/Feature/Rank/CorrectPersonRanksTest.php; обновить существующий RefillPersonRanksCommand.php как полный rebuild (FR-007, FR-011).
- [x] T042 [US4] Добавить/обновить tests/Feature/Rank/RefillPersonRanksTest.php: CLI, несколько активных спортсменов и прежняя область отбора, ручные даты, прежний аудит, повтор и rollback на спортсмена (FR-002, FR-004, FR-009, FR-011).

## Phase 7: Polish and validation

- [x] T043 Обновить существующую запись 049 resources/spa/pages/updates/updates.ts и связанные ожидания; удалить оставшиеся production references старой модели, проверить карту specs/049-rank-calculation-audit/review.md (FR-001–FR-014).
- [x] T044 Выполнить узкие integration/API/SPA проверки изменённого поведения; исправить найденные несогласованности и сверить acceptance scenarios specs/049-rank-calculation-audit/spec.md (FR-001–FR-014).
- [x] T045 Однократно выполнить финальные composer test/stan/cs/rector dry-run, npm run ci, локальный HTTP smoke, git diff --check и проверить indexed list без N+1 согласно specs/049-rank-calculation-audit/quickstart.md (FR-001–FR-014).
- [x] T046 Обновить статус/результаты в specs/049-rank-calculation-audit/spec.md, plan.md, tasks.md и research.md; подтвердить отсутствие запуска rebuild рабочих данных (SC-001–SC-007).

## Dependencies and execution

T029 → T030 → T031 → US1 → US2 → US3 → US4 → polish. Тесты изменения поведения выполняются перед/вместе с реализацией и должны подтверждать новую модель. T037 дополняет первоначальные integration проверки T031 после переключения callers. T040 может выполняться независимо от backend T038–T039 после фиксации контрактов. Изменения общих файлов выполняются последовательно. Полные гейты только T045; между фазами узкие тесты.

## Implementation strategy

Первый результат: одно атомарное успешное применение с одним действием Person. Затем все причины/входы и API/UI, после них полный refill. Не вводить новые сервисы ради переноса снимков или ошибок; удалять освобождённые классы и тесты прежней модели. Все четыре истории обязательны для завершения фичи.

## Итог

Все 18 задач T029–T046 выполнены. Результаты финальных проверок и обнаруженный/исправленный путь объединения событий описаны в plan.md. Прежняя модель не является текущим контрактом; tasks-history.md хранит только историю.
