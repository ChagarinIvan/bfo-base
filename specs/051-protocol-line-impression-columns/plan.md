# Implementation plan: Колонки аудита строк протокола

**Branch**: `051-protocol-line-impression-columns` | **Date**: 2026-11-01 | **Spec**: [spec.md](spec.md)

## Summary

Передать существующий аудит ProtocolLine в DTO результатов и отобразить две
опциональные колонки на страницах события, спортсмена и этапа кубка.
Переиспользовать ListingTable и ImpressionDetails. Данные только для authenticated.

## Technical context

- PHP 8.5, Laravel 13, Symfony-free собственные DTO Groups/serializer.
- Vue 3, TypeScript, PrimeVue, Vitest/happy-dom; зависимости не добавляются.
- MySQL 8.4 без изменения схемы; аудит уже хранится после фичи 050.
- Настройки колонок в localStorage, раздельные tableId и auth namespace.
- Тесты PHPUnit request/integration и Vitest с реальным ListingTable.
- Область: два read DTO, два assemblers, три страницы, их тесты и updates.
- Нагрузка: аудит читается из атрибутов строки, без дополнительных SQL relations;
  справочник авторов загружается списком и использует существующий cache/refresh.

## Constitution check

До и после проектирования PASS. Application assemblers преобразуют Domain
Impression через AuthAssembler. Bridge serializer выдаёт private DTO fields
по Groups(['authenticated']). Новых сервисов, ports, queries и миграций нет.
Используются существующие read commands, Criteria/Resources и пагинация.
Агрегат и владелец аудита ProtocolLine не меняются. Нет write transition,
транзакции записи, события, after-commit handler, cache invalidation или
изменения удаления. Legacy services/repositories не расширяются.
Новый код импортирует PHP-имена через use. Request tests имеют class-level @see.
Unit tests работают с mock collaborators; реальные данные только в API tests.
Белорусские labels через существующие i18n keys. В конце обязательны PHP gates,
frontend CI, local app smoke, N+1 review и запись updates.

## Project structure

```text
specs/051-protocol-line-impression-columns/
  spec.md, plan.md, research.md, data-model.md, quickstart.md, tasks.md
  contracts/read-and-columns.md, checklists/requirements.md, validation.md
app/Application/Dto/
  ProtocolLine/{ProtocolLineAssembler,ViewProtocolLineDto}.php
  Cup/CupAssembler.php
  CupEvent/ViewCupEventPointDto.php
resources/spa/
  api/types.ts
  pages/{events/EventViewPage,persons/PersonViewPage,cups/CupEventViewPage}.vue
  pages/protocolLineImpressions.test.ts
  pages/updates/{updates.ts,updates.test.ts}
tests/Feature/Api/V1/
  ProtocolLine/ListProtocolLinesActionTest.php
  Cup/ListCupEventPointsActionTest.php
```

## Design and execution

1. Добавить обязательные ImpressionDto created/updated с authenticated groups
   в оба DTO, mapping существующих атрибутов через AuthAssembler.
2. Колонки `created`/`updated` с defaultVisible false только при auth. Сохранить
   tableId и остальные defaults. Слоты/Column используют ImpressionDetails.
3. PersonViewPage получает справочник best-effort только при auth. В Event и
   CupEvent ошибки getUsers возвращают пустой список вместо отказа страницы.
4. CupEventViewPage передаёт authenticated. Для ранее сохранённого guest key
   скопировать набор в authenticated key только если тот отсутствует; оставить
   guest key, не переписывать существующий authenticated выбор. Операция
   best-effort, локальная для этой страницы, до монтирования ListingTable.
5. Reset означает очистку сохранённого ключа и повторное открытие, как у
   действующих настроек. Новую кнопку сброса эта фича не добавляет.
6. Покрыть authenticated/guest API, источник аудита, UI выбор/сохранение/старые
   настройки/logout/пустые строки/failure справочника. Повторно использовать
   существующие tests ImpressionDetails для fallback и подробностей.
7. Узкие тесты после фаз, полные проверки один раз в конце. Converge сверяет
   все FR/SC/AC и добавляет задачи только при выявленных пробелах.

## Complexity tracking

Нарушений конституции и новых инфраструктурных абстракций нет. Перенос старого
ключа ограничен страницей этапа кубка, где раньше отсутствовал auth namespace.
