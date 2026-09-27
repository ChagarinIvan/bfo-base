# Tasks: Backend architecture manifest

**Input**: [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), [contracts/architecture-manifest.md](contracts/architecture-manifest.md).

**Tests**: Код приложения не меняется. Задачи на новые автоматические тесты в этот цикл не входят.

## Phase 1: Setup

**Purpose**: Подготовить документационный цикл и сохранить границы работ.

- [x] T001 Создать `specs/040-backend-architecture-manifest/spec.md` и связать фичу через `.specify/feature.json`.
- [x] T002 Зафиксировать три рабочих архитектурных решения и оценить требования в `specs/040-backend-architecture-manifest/checklists/requirements.md`.

## Phase 2: Foundational

**Purpose**: Собрать проверяемые исходные данные и нормативную основу.

- [x] T003 [P] Исследовать слои, event flow, soft delete и legacy по исходным файлам в `specs/040-backend-architecture-manifest/research.md`.
- [x] T004 [P] Подготовить `specs/040-backend-architecture-manifest/plan.md`, `data-model.md`, `contracts/architecture-manifest.md` и `quickstart.md`.
- [x] T005 Обновить `.specify/memory/constitution.md` до 2.6.0 и закрепить обязательный манифест, аудит, события и удаление.

## Phase 3: User Story 1 - Единые правила новой фичи (Priority: P1)

**Goal**: Агент и ревьюер получают один обязательный архитектурный документ.

**Independent Test**: По `AGENTS.md`, `CLAUDE.md` и конституции найден один манифест; по нему можно оценить новый write use case.

- [x] T006 [US1] Описать приоритет источников, границы слоёв и обязательный путь новой серверной фичи в `.specify/memory/backend-architecture-manifest.md`.
- [x] T007 [US1] Описать правила command, DTO, Criteria/Resources, transaction, audit, event и API boundary в `.specify/memory/backend-architecture-manifest.md`.
- [x] T008 [P] [US1] Добавить обязательное чтение манифеста перед фичей и ревью в `AGENTS.md`.
- [x] T009 [P] [US1] Добавить такое же правило в `CLAUDE.md`, не копируя весь манифест.
- [x] T010 [US1] Добавить в `.specify/memory/backend-architecture-manifest.md` проверочный список для спецификации и PR review.

## Phase 4: User Story 2 - Обзор текущего бэкенда (Priority: P1)

**Goal**: Показать действительные потоки, сильные паттерны и места долга.

**Independent Test**: Каждый ключевой вывод можно проверить по указанному исходному файлу, а у каждого приоритетного долга есть ограниченный следующий шаг.

- [x] T011 [US2] Описать карту модулей и пять ключевых потоков в `.specify/memory/backend-architecture-manifest.md` с путями к исходному коду.
- [x] T012 [US2] Добавить паттерны, антипаттерны и инвентаризацию `app/Services` и `app/Repositories` в `.specify/memory/backend-architecture-manifest.md`.
- [x] T013 [US2] Составить приоритетный реестр `REF-*` в `.specify/memory/backend-architecture-manifest.md` с рисками, зависимостями и способом проверки.

## Phase 5: User Story 3 - Жизненный цикл агрегата (Priority: P2)

**Goal**: Сделать политику событий, аудита и удаления применимой к текущим агрегатам.

**Independent Test**: Матрица позволяет определить соответствие `Event`, `Group`, `Club`, `Competition`, `ProtocolLine` и `RankCheck` и явные исключения.

- [x] T014 [US3] Составить матрицу агрегатов и известных исключений в `.specify/memory/backend-architecture-manifest.md`.
- [x] T015 [US3] Описать запись, публикацию, commit, доставку, повтор и ошибки обработчиков в `.specify/memory/backend-architecture-manifest.md`.
- [x] T016 [US3] Описать soft delete, фильтрацию видимости и допустимое физическое удаление в `.specify/memory/backend-architecture-manifest.md`.

## Phase 6: Polish & cross-cutting concerns

- [x] T017 Проверить `specs/040-backend-architecture-manifest/quickstart.md`, пути в манифесте, ссылки инструкций и отсутствие изменений runtime-кода.
- [x] T018 Провести `$speckit-analyze`, устранить несогласованность артефактов и затем `$speckit-converge` для `specs/040-backend-architecture-manifest/tasks.md`.

## Dependencies & Execution Order

- Phase 1 и Phase 2 завершены. Phase 3 начинает манифест; T008 и T009 независимы после определения его пути.
- Phase 4 и Phase 5 добавляют разные разделы одного файла, поэтому правки в манифест выполняются последовательно.
- Phase 6 зависит от всех трёх пользовательских сценариев.

## Parallel Example: User Story 1

После T006 параллельно можно подготовить ссылки в `AGENTS.md` (T008) и `CLAUDE.md` (T009); T007 и T010 меняют один файл и выполняются последовательно.

## Implementation Strategy

Сначала завершить правила и ссылки US1, затем обзор US2 и матрицу US3. Последняя проверка сверяет все три сценария с исходным кодом. Массовые рефакторинги из `REF-*` становятся отдельными спецификациями.
