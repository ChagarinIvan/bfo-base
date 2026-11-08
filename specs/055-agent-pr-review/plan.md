# Implementation plan: Agent PR review

**Branch**: `055-agent-pr-review` | **Date**: 2026-11-08 | **Spec**: [spec.md](spec.md)

## Summary

Шесть matrix jobs Codex Action и отдельный publisher. pull_request_target только
для same-repository non-draft PR в master. Trusted controls checkout base SHA;
candidate checkout head SHA только как данные, агент работает из trusted directory.

## Technical context

GitHub-hosted Ubuntu, Node 22 built-ins, официальный openai/codex-action.
JSON schema, Markdown prompts; новые npm/composer зависимости не нужны.
Модель задаётся repository variable CODEX_REVIEW_MODEL, иначе default Action;
effort medium, 15 минут на роль, max-parallel 2, 12 findings на роль.
До 150 файлов и 1 MiB patch; превышение явно отражается как partial, patch не обрезается молча.
Выход до 128 KiB на роль и 60 000 символов на комментарий, artifacts хранятся 7 дней.
Расходы ограничиваются timeout/concurrency и бюджетом OpenAI project, не обещанием token cap.

## Constitution check

PASS до research и после design. Production PHP/DDD/HTTP поведение не меняется,
новых запросов/агрегатов нет. CI tooling расположен в .github/review, не в legacy.
Тестируем schema validation, partial/error, dedup, escaped output и publisher freshness.
Updates entry обязательна; существующие CI-гейты сохраняются.

## Project structure

- .github/workflows/agent-review.yml: matrix analysis и publisher.
- .github/review/{common.md,roles/*.md,report.schema.json}: доверенные правила.
- .github/review/{review.cjs,review.test.cjs}: prepare, validation, aggregation, publish.
- .github/review/README.md: подключение и проверка.
- resources/spa/pages/updates/updates{,.test}.ts: запись 055.
- specs/055-agent-pr-review/: артефакты цикла.

## Phases

Research -> schema/prompts -> tests/aggregation -> workflow -> docs/updates -> validation.
Ревью плана выполнено перед tasks: соответствует FR-001–012; пользователь заранее
попросил пройти весь цикл. Hooks отсутствуют. Расширения области нет.

## Complexity tracking

Новых архитектурных исключений нет. Отдельная публикация нужна для разделения прав.
SC-005 требует реального GitHub запуска с ключом; локально не симулируем успех модели.
