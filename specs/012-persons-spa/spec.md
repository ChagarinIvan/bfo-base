# Feature Specification: SPA-страница персонов

**Feature Branch**: `012-persons-spa`

**Created**: 2026-09-03

**Status**: Draft

**Input**: User description: "Глобально перевести страницу персонов на SPA, удалить старый API-роут и связанное легаси, переиспользовать таблицу со страницы клуба, добавить поиск и фильтры."

## Clarifications

### Session 2026-09-03

- Q: Что должно происходить при открытии старого URL списка `/persons` после перехода на SPA? → A: Старый маршрут удаляется и отвечает 404.
- Q: После удаления старых API-маршрутов нужно ли также переписать внутренние console/event/cup-сценарии, чтобы полностью удалить `LegacySearchPersonDto` и `ListLegacyPersonsService`? → A: Да, переписать потребителей и удалить весь legacy DTO/service списка персонов.
- Q: Какие клубы должны быть доступны в фильтре клуба на странице персонов? → A: Только активные клубы, возвращаемые V1 API; справочник может использовать облегчённые поля `id/name` и кэшироваться.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Просмотр и поиск персонов (Priority: P1)

Пользователь открывает раздел персонов в SPA и видит постраничную таблицу активных персонов с фамилией, именем, клубом, годом рождения и текущим разрядом. Он может искать по фамилии и имени и комбинировать поиск с фильтрами.

**Why this priority**: Это основной пользовательский сценарий и замена существующей страницы списка.

**Independent Test**: Открыть `/app/persons`, дождаться загрузки, применить каждый фильтр отдельно и в комбинации, перейти на другую страницу результатов.

**Acceptance Scenarios**:

1. **Given** пользователь открывает раздел персонов, **When** данные загружены, **Then** отображается общая таблица с пагинацией и ссылками на legacy-просмотр каждого персоны.
2. **Given** введена часть фамилии или имени, **When** поиск выполнен, **Then** отображаются только персоны, чья фамилия или имя содержит искомую строку без требования полного совпадения.
3. **Given** выбраны разряд, год рождения и клуб, **When** поиск выполнен, **Then** выдача соответствует всем выбранным условиям одновременно.
4. **Given** условия не дали результатов, **When** загрузка завершена, **Then** показывается понятное пустое состояние без ошибки.

### User Story 2 - Переход к действиям персоны (Priority: P2)

Авторизованный пользователь может нажать «Дадаць асобу» и перейти к существующей legacy-форме создания персоны. Ссылки на просмотр, редактирование и прочие старые разделы продолжают вести на соответствующие legacy-маршруты.

**Why this priority**: SPA заменяет только список; рабочие операции создания и управления должны остаться доступными во время поэтапной миграции.

**Independent Test**: Войти в систему, открыть SPA-список, проверить кнопку создания и ссылки строки; убедиться, что неавторизованный пользователь не получает действие создания.

**Acceptance Scenarios**:

1. **Given** пользователь авторизован, **When** он нажимает «Дадаць асобу», **Then** открывается существующая legacy-форма `/persons/create`.
2. **Given** пользователь открывает строку персоны, **When** он нажимает имя или фамилию, **Then** открывается legacy-просмотр `/persons/{id}/show`.
3. **Given** пользователь не авторизован, **When** он открывает список, **Then** список доступен для просмотра, а кнопка создания и защищённые действия не отображаются.

### User Story 3 - Единая таблица и удаление легаси списка (Priority: P2)

Команда получает один переиспользуемый компонент таблицы персонов для списка персонов и блока персонов клуба. Старый Vue2-список, его шаблон, неверсированные API-входы и выделенный legacy DTO/service списка удаляются; внутренние сценарии переводятся на целевые use case/критерии, а legacy-страницы деталей и форм сохраняются.

**Why this priority**: Уменьшает дублирование и исключает два конкурирующих API/клиента списка.

**Independent Test**: Проверить, что обе SPA-страницы используют один компонент таблицы, старые маршруты списка/API отсутствуют, а страницы просмотра/создания/редактирования персоны работают.

**Acceptance Scenarios**:

1. **Given** открыты список персонов и детали клуба, **When** отображается таблица участников, **Then** используется одинаковая структура колонок и поведения ссылок.
2. **Given** приложение собрано, **When** проверяются маршруты и ассеты, **Then** Vue2-компонент списка и старые API-контроллеры списка не подключаются.
3. **Given** пользователь переходит на legacy-просмотр или форму персоны, **When** страница загружается, **Then** эти маршруты продолжают работать независимо от удаления списка.

### Edge Cases

- Пустой или состоящий из пробелов поисковый запрос сбрасывает поиск; короткий ввод не должен создавать лишние запросы.
- Фильтр года содержит годы от 1920 до текущего года включительно; отсутствие года означает «любой год».
- У персоны может отсутствовать клуб или год рождения; таблица показывает пустое значение и не ломает фильтрацию.
- Быстрые изменения фильтров не должны показывать устаревший ответ поверх более нового.
- Ошибка API отображается как состояние ошибки с возможностью повторить загрузку.
- Комбинация фильтров с пустой выдачей сохраняет выбранные условия и корректную пагинацию.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide the canonical persons listing at `/app/persons` in the SPA; the old listing URL `/persons` MUST be unregistered and return 404.
- **FR-002**: System MUST load the listing from the existing versioned persons API and preserve server-side pagination.
- **FR-003**: The persons API/listing MUST support a case-insensitive partial search across both lastname and firstname.
- **FR-004**: The listing MUST support independent rank, birth-year, and club filters and apply them cumulatively.
- **FR-005**: The birth-year selector MUST offer every year from 1920 through the current year and a no-filter option.
- **FR-006**: The table MUST display person name, club, birth year, current rank, and available row actions; missing optional values MUST be rendered safely.
- **FR-007**: The persons listing and club details MUST reuse one shared person-table component with consistent legacy links.
- **FR-008**: Authenticated users MUST see an «Дадаць асобу» action that opens the existing legacy person creation route; unauthenticated users MUST not see it.
- **FR-009**: The SPA MUST keep links to person view, edit, and other existing legacy sections functioning during this migration.
- **FR-010**: The old non-versioned person list API routes (`/api/person` and `/api/persons`) and their list-only controllers, DTOs, and services MUST be removed; all console/event/cup consumers MUST be migrated before removal, and `/api/v1/persons` MUST remain the single API for the SPA list.
- **FR-011**: The old Vue2 persons-list component, its list-only Blade mount/template code, and unused assets or translations MUST be removed without deleting legacy person detail/create/edit functionality.
- **FR-012**: The versioned persons response MUST expose all fields needed by the shared table and filters, including club display data and current rank data, without per-row follow-up requests. The club filter MUST use only active clubs from the versioned clubs endpoint; its selector may consume a cached lightweight `id/name` representation.
- **FR-013**: Loading, empty, validation, and API-error states MUST be visible and must not leave stale results after a newer request completes.
- **FR-014**: The implementation MUST preserve existing authentication and authorization rules for legacy person actions and avoid introducing new N+1 queries.

### Key Entities *(include if feature involves data)*

- **Person list row**: An active person with identifier, name, optional birth year, club information, current rank, and optional audit metadata.
- **Person search criteria**: Optional text query, rank identifier, birth year, club identifier, page, and page size.
- **Shared person table**: A reusable presentation of person rows with links to legacy person routes and consistent authenticated actions.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of navigation links intended for the persons listing open `/app/persons`; `/persons` is unregistered and returns 404.
- **SC-002**: A user can apply any combination of the four criteria (name, rank, birth year, club) and receive a result set satisfying all selected criteria.
- **SC-003**: The first page of the persons list requires no per-row network requests after the list response and renders within the existing SPA listing performance budget.
- **SC-004**: Existing automated coverage verifies the SPA route, API query parameters, filters, shared table usage, authorization visibility, and legacy-route preservation.
- **SC-005**: The removed Vue2 list and non-versioned API routes have zero remaining production references, while legacy person details and forms remain reachable.

## Assumptions

- Person details, creation, editing, deletion, prompts, payments, and rank sections remain legacy routes in this feature; only the listing moves to SPA.
- The legacy `/persons` listing route is intentionally not redirected; only detail and action routes under `/persons/{id}/...` remain available.
- The existing `/api/v1/persons` endpoint remains available to anonymous viewers under its current optional-authentication policy.
- Internal console, event, and cup scenarios are in scope for migration away from the removed legacy person-list DTO/service.
- Rank and club options are sourced from existing versioned SPA endpoints; the current calendar year is the upper bound for birth-year options.
- The club selector loads all active clubs from the dedicated V1 all-items endpoint and may cache the lightweight `id/name` response; the endpoint has no artificial item limit.
- The existing SPA authentication store and navigation shell are reused.
- Existing language keys may be extended for SPA-specific labels; unrelated legacy translations are out of scope unless proven unused after removal.
