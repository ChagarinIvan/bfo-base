# Tasks: Agent PR review

## Phase 1: Setup

- [x] T001 Clarify и spec review в specs/055-agent-pr-review/spec.md, checklist 9/9.
- [x] T002 Research, plan review, data model, contracts и quickstart в specs/055-agent-pr-review/.

## Phase 2: Foundation

- [x] T003 Добавить regression tests для report validation/dedup/failure/freshness в .github/review/review.test.cjs, сначала red.
- [x] T004 Реализовать schema и trusted prepare/validate/render/publish в .github/review/{report.schema.json,review.cjs}.

## Phase 3: US1, P1, специализированные проверки

Independent test: schema и шесть инструкций покрывают FR-001–008.

- [x] T005 [US1] Создать .github/review/common.md и roles/{architecture,ddd,security,tests,frontend,uiux}.md с проектными источниками.
- [x] T006 [US1] Добавить matrix review job в .github/workflows/agent-review.yml: immutable refs, sandbox, limits, trusted rules.

## Phase 4: US2, P1, общий отчёт

Independent test: mixed artifacts, duplicates, invalid/missing output, stale/closed PR.

- [x] T007 [US2] Подключить artifacts и отдельный publisher в .github/workflows/agent-review.yml; публикация и обновление marker через review.cjs.
- [x] T008 [US2] Проверить локальный green для .github/review/review.test.cjs и статическую безопасность workflow.

## Phase 5: US3, P2, настройка

Independent test: владелец может установить workflow и отличить отсутствие ключа от clean.

- [x] T009 [US3] Документировать key/model/permissions/triggers/cost/rollout в .github/review/README.md.
- [x] T010 [US3] Добавить одну запись 055 в resources/spa/pages/updates/updates{,.test}.ts.

## Phase 6: Validation

- [x] T011 Запустить подходящие проверки, review артефактов и записать specs/055-agent-pr-review/validation.md.
- [ ] T012 Реальный GitHub smoke после подключения credentials, зафиксировать SC-005 в validation.md.

## Dependencies and strategy

T001 -> T002 -> T003 red -> T004 -> T005 -> T006 -> T007 -> T008 -> T009 -> T010 -> T011 -> T012.
MVP: один typed report и publisher; итоговая реализация включает все шесть ролей.
Независимые роли/документацию можно писать параллельно после контракта;
реализация в этой сессии последовательная. External smoke не подменяется local stub.
