# Implementation Plan: Асинхронная проверка разрядов по списку

**Branch**: `026-rank-list-check` | **Date**: 2026-09-16 | **Spec**: [spec.md](spec.md)

## Summary

Добавить read-only запуск `RankCheck`, доступный только аутентифицированным API-пользователям.
API принимает CSV и сразу возвращает `202` с ID и `PARSING`; обработчик доменного события `RankCheckCreated`, выполняемый через очередь, запускает исторический
pipeline парсинга/идентификации/получения разряда через domain processor, сохраняет снимок строк и переводит
запуск в `READY` или `FAILED`. SPA предоставляет навигационный пункт, форму, polling и
семиколоночный результат старой проверки с серверной пагинацией через отдельный rows endpoint.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript/Vue 3

**Primary Dependencies**: Laravel 13, Redis queue/Horizon, Eloquent, existing parser and person/rank services behind feature ports

**Storage**: MySQL for `RankCheck`/`RankCheckRow`; existing private file storage for source file; Redis for queue; daily scheduler for retention

**Testing**: PHPUnit request/application/domain tests; Vitest SPA tests; project CS, PHPStan, Rector and frontend gates

**Target Platform**: Existing Laravel API and SPA deployment

**Project Type**: Web application with API and SPA

**Performance Goals**: Create request returns within 2 seconds independent of list processing time; status visible within 5 seconds of final transition

**Constraints**: No synchronous full-list processing; no mutation of person/rank data; no partial rows exposed; bounded polling and authentication; result pages must be server-paginated

**Scale/Scope**: One independent run per upload; arbitrary supported list size within configured upload/storage/queue limits; v1 has no export or notifications

## Constitution Check

- Target layers: Domain entity/status/repository port; Application commands/services; Bridge actions/job/routes; Infrastructure Eloquent/file adapters.
- No new legacy `app/Services` or `app/Repositories` extension; existing parser/identification behavior is wrapped or injected through ports where needed.
- Application/Domain unit tests use mocks, not Eloquent entities; persistence and HTTP contracts use integration/request tests.
- Queue state transitions are explicit and transactional; errors are observable; source and personal data are excluded from ordinary logs.
- No new dependency is required.

## Project Structure

```text
app/Domain/RankCheck/
app/Application/Service/RankCheck/
app/Application/Port/RankCheck/
app/Bridge/Laravel/Http/Controllers/Api/V1/RankCheck/
app/Bridge/Laravel/Jobs/
app/Bridge/Laravel/Console/Commands/
app/Infrastructure/Laravel/Eloquent/RankCheck/
app/Infrastructure/RankCheck/
database/migrations/
resources/spa/api/rankChecks.ts
resources/spa/pages/rank-checks/
tests/Domain/RankCheck/
tests/Application/Service/RankCheck/
tests/Feature/Api/V1/RankCheck/
resources/spa/**/*.test.ts
```

**Structure Decision**: Follow the existing target-layer architecture. Application services
coordinate repositories, shared `Storage`, factories, transactions and assemblers. The preserved
parser is wrapped by `StandardRankListParser`, which returns normalized `RankListItem` DTOs. The
domain `StandardRankCheckProcessor` performs matching, snapshotting and row creation; its failures
are represented by `ProcessError`, handled by `RankCheck::process()`, and persisted by the service.
Person prompt lookup and the legacy fallback are encapsulated by `StandardRankCheckPersonMatcher`.
Eloquent, file storage, Laravel queue and scheduler details remain in Infrastructure/Bridge.
Status and rows are separate reads: `ViewRankCheckDto` uses the existing `AuthAssembler` for
`created`/`updated`, while the rows action returns the standard paginated `Slice`. Bindings are
isolated in `RankCheckProvider`, and generic file `Storage` is bound in `SharedProvider`.

## Complexity Tracking

No constitution violations identified.
