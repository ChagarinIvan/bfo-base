# Implementation Plan: расчёт и простой аудит разрядов

**Branch**: `master` | **Date**: 2026-10-24 | **Spec**: [spec.md](spec.md)

## Summary

Общая RankActivationPolicy применяет правило подтверждённого равного/высшего разряда. PersonRanksUpdater в Domain собирает факты, рассчитывает состояние, меняет Person и сохраняет аудит. RebuildPersonRanksService в Application только блокирует спортсмена и координирует транзакцию; импорт использует тот же доменный updater. Журнал содержит trigger, created и два состояния, без дублирующей диагностики.

## Technical Context

PHP 8.5 / Laravel 13, Vue 3 / TypeScript / PrimeVue, MySQL 8.4. Новых зависимостей нет. PHPUnit покрывает политику, доменные значения, оркестрацию и реальные persistence/API сценарии; Vitest проверяет журнал. Чтение через индекс person_id/id и Slice perPage+1 без COUNT. Обход спортсменов для коррекции потоковый.

## Constitution Check

- Новые domain value objects типизированы и не используют произвольные словари. Trigger является enum.
- Application не реализует Domain port и не содержит расчёт/формирование аудита.
- В транзакции блокируется Person; доменный updater изменяет его через updateRanks с Impression и событием, затем сохраняет Person и аудит. При равном результате нет мутации/записи.
- Ошибка оборачивается типизированным RankRebuildFailure. После отката координатор вызывает доменную запись ошибки; в журнал не попадает текст внутренних исключений.
- Импортный updater работает в своей границе на одного спортсмена, сохраняя existing after-commit pipeline.
- Новая техническая запись аудита не наследует Eloquent; модель хранения только в Infrastructure. Удаление производной истории не удаляет аудит.
- ProtocolLine создаётся с created/updated из Impression текущей операции. Общий ImpressionCast возвращает null для старых неизвестных значений.
- API action тонкий; command/service/assembler, стандартный AuthAssembler для created и Slice для списка. SPA на белорусском; доступ к журналу требует авторизации.
- Финальные backend/frontend гейты, локальная запускаемость, отсутствие новых N+1 и проверка acceptance обязательны.

## Project Structure

- Domain/Person: RankCalculator, RankActivationPolicy, PersonRanksUpdater.
- Domain/Person/Audit: RankAuditEntry, RankAuditTrigger, RankSnapshot, RankHistorySnapshot, RankAuditRepository, RankRebuildFailure.
- Application/Service/Person: короткий RebuildPersonRanksService, preview/apply коррекция и paginated read.
- Infrastructure/Laravel/Eloquent/Person/RankAudit: модель, repository и mapper снимков для JSON; без бизнес-решений.
- Bridge: API списка журнала, rank jobs/CLI и маршруты; передают enum триггера.
- resources/spa/pages/persons/PersonRankAudit.vue и api/personRankAudit.ts: created, trigger, до/после и постраничный просмотр.
- database/migrations/2026_10_24_000001_add_rank_audit.php: минимальная таблица журнала и полный аудит ProtocolLine.

## Validation

Regression-first для дат и активации, затем persistence/rollback/no-op, API authorization/pagination, UI states, preview/apply и сохранность created. Полный набор один раз после последних изменений; повтор узких проверок при выявленных ошибках.
