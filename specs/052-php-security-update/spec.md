# Feature Specification: Обновление безопасности PHP и Composer-зависимостей

**Feature Branch**: `052-php-security-update`
**Created**: 2026-11-01
**Status**: Implemented and locally validated; CI/server PHP 8.5.12 verification pending
**Input**: Новый spec-kit, обновить PHP из-за последних уязвимостей и выполнить composer update. Локальный PHP без Docker; Docker используется на сервере. Серверную пересборку и composer install выполняет пользователь. Файлы spec-kit и обновление зависимостей должны входить в PR #178. Последующее поручение пользователя требует применить Rector и получить зелёный результат всех локальных гейтов.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Исправленный PHP на сервере (Priority: P1)

Администратор собирает приложение с исправленным PHP и получает ту же версию в CI.

**Why this priority**: Исправляет уязвимости интерпретатора веб-запросов.
**Independent Test**: Проверить build inputs, а после выкладки фактический runtime.

**Acceptance Scenarios**:

1. **Given** серверный Dockerfile, **When** собирают app с pull базового образа,
   **Then** используется конкретный PHP 8.5.12 с официальными исправлениями безопасности.
2. **Given** CI, **When** запускаются существующие проверки PR, **Then** они используют PHP 8.5.12.
3. **Given** рекомендация PHP по session upload progress, **When** стартует новый
   контейнер, **Then** настройка отключена до разбора запроса, в том числе при
   монтировании конфигурационного каталога. Обычные загрузки файлов сохраняются.
4. **Given** локальная разработка, **When** готовят серверное обновление,
   **Then** локальный PHP не переустанавливается и Docker локально не запускается.

### User Story 2 - Обновлённые зависимости в lock (Priority: P1)

Разработчик обновляет Composer-зависимости, администратор устанавливает точные
версии из lock при выкладке.

**Why this priority**: Composer update является отдельной частью запроса.
**Independent Test**: Обзор изменений lock, validate, platform requirements и install.

**Acceptance Scenarios**:

1. **Given** текущие constraints, **When** выполнен полный composer update,
   **Then** совместимые production и dev-зависимости обновлены в composer.lock.
2. **Given** обновлённый lock, **When** выполняют composer install,
   **Then** версии воспроизводятся без нового dependency resolution и изменения lock.
3. **Given** audit, **When** проверяют зависимости, **Then** известные advisories
   и abandoned packages отражены в отчёте без выдуманного успешного полного аудита.
4. **Given** локальный PHP 8.5.10, **When** обновляются библиотеки,
   **Then** платформа проверяется фактически, без config.platform и ignore-platform flags.

### User Story 3 - Полный комплект изменения для review и выкладки (Priority: P2)

Reviewer получает спецификацию, план, задачи, контракт, результаты доступных
проверок и понятные команды серверной установки.

**Why this priority**: Один Dockerfile не покрывает запрос пользователя.
**Independent Test**: Проверить содержимое ветки PR и инструкцию выкладки.

**Acceptance Scenarios**:

1. **Given** PR #178, **When** reviewer открывает diff, **Then** видит Dockerfile,
   lock, CI, startup configuration, README, запись updates и все spec-kit артефакты.
2. **Given** серверная инструкция, **When** администратор повторяет её,
   **Then** собирает новый образ, устанавливает зависимости из lock и перезапускает workers.
3. **Given** применённые изменения обновлённого Rector, **When** запускаются
   локальные гейты, **Then** CS, PHPStan, Rector dry-run, полный PHP-набор и
   frontend CI проходят, а не проверенный production runtime указан отдельно.

### Edge Cases

- Composer audit не заменяет проверку версии самого PHP.
- Неподдерживаемая транзитивная dev-зависимость не скрывается как отсутствие проблем.
- Поднятый tag не обновляет уже запущенные FPM и queue workers.
- Volume mount conf.d должен сохранять доступность настройки безопасности.
- Composer install на сервере не обновляет пакеты, а воспроизводит подготовленный lock.
- Полные тесты остаются в CI; отсутствие локального прогона не объявляется зелёным результатом.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Серверный образ и CI MUST использовать исправленный PHP 8.5.12.
- **FR-002**: session.upload_progress.enabled=0 MUST применяться при startup
  серверного PHP и в CI согласно опубликованной рекомендации PHP.
- **FR-003**: MUST быть выполнен полный composer update в текущих constraints
  и сохранён обновлённый composer.lock, включая совместимые dev tools.
- **FR-004**: composer validate, check-platform-reqs и install MUST подтверждать
  согласованность файлов и воспроизводимость установленных версий.
- **FR-005**: Известные security advisories MUST отсутствовать в итоговом lock;
  abandoned packages MUST быть перечислены отдельно с источником зависимости.
- **FR-006**: Локальный runtime MUST не переустанавливаться повторно;
  platform overrides и игнорирование PHP requirements запрещены.
- **FR-007**: API, бизнес-правила и схема БД MUST сохраняться. Правки ограничены
  версиями, startup configuration, CI, документацией и необходимой совместимостью
  статического анализа и механической модернизацией Rector, без нового application behavior.
- **FR-008**: README MUST описывать сборку, install из lock, проверку фактического
  runtime и перезапуск FPM/queue workers при выкладке.
- **FR-009**: Фича MUST иметь полный комплект spec-kit артефактов и одну запись updates.
- **FR-010**: Validation MUST честно различать выполненные локальные гейты и ожидающую проверки серверную установку.

### Key Entities

- **Runtime contract**. Конкретная версия server/CI и совместимость локальной ветки 8.5.
- **Dependency lock**. Зафиксированные production/dev packages с версиями и hash.
- **Startup configuration**. Tracked ini-файл для образа и compose mount.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Server/CI используют одну исправленную patch-версию PHP.
- **SC-002**: Ноль известных security advisories в полном lock; все abandoned
  packages указаны явно, production audit проходит.
- **SC-003**: После install lock побайтно совпадает с состоянием перед install.
- **SC-004**: PR содержит все перечисленные артефакты и Composer lock.
- **SC-005**: Нет повторного локального обновления PHP; полнота проверок описана без ложного PASS.

## Assumptions

- Развивается обновление стека 001 отдельной фичей 052, исторические specs не меняются.
- Последнее поручение пользователя отменяет прежнюю паузу проверок: требуется
  полный локальный прогон гейтов после применения Rector.
- composer.json сохраняет ^8.5 для локальной совместимости. Требование исправленного
  server/CI runtime фиксируется точными версиями окружений; config.platform.php не вводится.
- Приложение не использует session upload progress или смену session serializer.
- Production deploy и проверку фактического server runtime выполняет пользователь.
- Нет новых агрегатов, commands, query ports, транзакций, событий и изменений удаления.
