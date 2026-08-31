# Feature Specification: Удаление FAQ-раздела

**Feature Branch**: `006-remove-faq`

**Created**: 2026-08-31

**Status**: Draft

**Input**: User description: "Удалить FAQ-раздел из нового SPA-приложения и старого Blade, очистить legacy Blade-шаблоны, связанные переводы и API, если у них нет другого использования."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Убрать FAQ из навигации (Priority: P1)

Пользователь видит только актуальные разделы приложения и не получает ссылок на
устаревший FAQ или страницу описания API.

**Why this priority**: Устаревшие ссылки создают ложное ожидание поддержки и
оставляют в интерфейсе раздел, который больше не является частью продукта.

**Independent Test**: Открыть SPA и legacy Blade-страницу с авторизованным
пользователем и убедиться, что в навигации отсутствуют FAQ и API FAQ, а остальные
пункты меню продолжают работать.

**Acceptance Scenarios**:

1. **Given** авторизованный пользователь открыл SPA, **When** он раскрывает меню
   справки, **Then** в меню отсутствуют пункты FAQ и API.
2. **Given** пользователь открыл legacy Blade-страницу, **When** он просматривает
   навигацию, **Then** в ней отсутствуют FAQ и API FAQ, а соседние разделы не
   изменились.
3. **Given** неавторизованный пользователь, **When** он открывает SPA или legacy
   страницу, **Then** он не видит FAQ-ссылки ни в одном доступном меню.

---

### User Story 2 - Закрыть устаревшие FAQ-страницы (Priority: P1)

Пользователь не может открыть удалённые FAQ-страницы по старым прямым адресам.

**Why this priority**: Удаление только ссылок оставило бы устаревший контент
доступным по закладкам, поисковой выдаче или внешним ссылкам.

**Independent Test**: Запросить прежние адреса FAQ и API FAQ и проверить, что они
не возвращают удалённые страницы, при этом существующие несвязанные адреса
продолжают отвечать как раньше.

**Acceptance Scenarios**:

1. **Given** пользователь запрашивает `/faq`, **When** приложение обрабатывает
   запрос, **Then** FAQ-страница не отображается и приложение возвращает свой
   стандартный ответ для отсутствующего адреса.
2. **Given** пользователь запрашивает `/faq/api`, **When** приложение обрабатывает
   запрос, **Then** страница описания API не отображается и приложение возвращает
   свой стандартный ответ для отсутствующего адреса.
3. **Given** пользователь запрашивает любой другой существующий маршрут, **When**
   приложение обрабатывает запрос, **Then** результат работы этого маршрута не
   меняется из-за удаления FAQ.

---

### User Story 3 - Удалить неиспользуемые остатки FAQ (Priority: P2)

Разработчик поддерживает кодовую базу без мёртвых FAQ-контроллеров, шаблонов,
навигационных флагов и переводов.

**Why this priority**: Удаление связанных остатков предотвращает повторное
появление FAQ через случайное переиспользование старых классов и ключей.

**Independent Test**: Выполнить поиск по репозиторию после изменения и убедиться,
что удалённые FAQ-артефакты не имеют ссылок, а фактически используемые общие
компоненты и API не затронуты.

**Acceptance Scenarios**:

1. **Given** удаление FAQ завершено, **When** разработчик проверяет навигационные
   модели и данные legacy-навигации, **Then** там нет FAQ-пунктов и специальных
   FAQ-флагов маршрута.
2. **Given** удаление FAQ завершено, **When** разработчик проверяет шаблоны,
   контроллеры и переводы, **Then** неиспользуемые FAQ-артефакты отсутствуют.
3. **Given** старый API endpoint используется не только FAQ-страницей, **When**
   выполняется очистка, **Then** endpoint сохраняется; удаляются только endpoints,
   которые после проверки не имеют других consumers.

## Edge Cases

- Прямой переход по `/faq` и `/faq/api` должен обрабатываться одинаково независимо
  от авторизации и языка интерфейса.
- Удаление FAQ не должно удалить общие ключи переводов, которые используются
  другими страницами.
- Удаление FAQ API-документации не должно автоматически удалять реальные API
  endpoints, если на них ссылаются другие страницы, интеграции или тесты.
- Legacy navbar не должен потерять соседние разделы или изменить их доступность.
- В SPA не должно остаться переходов на удалённые FAQ-адреса через fallback,
  меню или тестовые fixtures.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Система MUST удалить FAQ и API FAQ из навигации нового SPA.
- **FR-002**: Система MUST удалить FAQ и API FAQ из legacy Blade-навигации.
- **FR-003**: Система MUST прекратить публикацию страниц по адресам `/faq` и
  `/faq/api`; прямой запрос к ним MUST получать стандартный ответ для
  отсутствующего маршрута.
- **FR-004**: Система MUST удалить шаблоны и контроллеры, предназначенные только
  для FAQ и API FAQ.
- **FR-005**: Система MUST удалить специальные данные и флаги навигации,
  предназначенные только для определения FAQ-маршрутов.
- **FR-006**: Система MUST удалить переводы, используемые только удалёнными FAQ
  страницами и их пунктами навигации.
- **FR-007**: Перед удалением API-related кода система MUST проверить его
  repository-wide usage; фактически используемые API endpoints MUST remain
  available.
- **FR-008**: Система MUST preserve all non-FAQ navigation entries, routes,
  translations and API behavior.
- **FR-009**: Система MUST include automated regression coverage for the absence
  of FAQ navigation and the unavailability of the removed legacy pages.
- **FR-010**: Система MUST leave no references to removed FAQ routes, controllers,
  templates or translation keys, except in the feature specification and
  migration/history documentation where the removal is explicitly described.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of SPA and legacy navigation assertions contain no FAQ or API
  FAQ entries after the change.
- **SC-002**: 100% of direct requests to `/faq` and `/faq/api` receive the
  standard missing-route response and render no FAQ content.
- **SC-003**: Repository search finds zero active-code references to removed FAQ
  controllers, templates, route flags or FAQ-only translation keys.
- **SC-004**: All existing automated tests for non-FAQ routes remain green after
  the removal.
- **SC-005**: No real API endpoint is removed when it has at least one consumer
  outside the deleted FAQ documentation page.

## Assumptions

- The removal is intentional and permanent for both the SPA navigation and the
  legacy Blade interface.
- No redirect or replacement help page is required; removed addresses use the
  application's normal missing-route behavior.
- The FAQ content is not migrated to a new help center as part of this feature.
- “API FAQ” refers to the legacy documentation page and its route; actual API
  endpoints are removed only when repository-wide usage confirms they are
  unused.
- Existing authentication, localization infrastructure and unrelated navigation
  are reused unchanged.
