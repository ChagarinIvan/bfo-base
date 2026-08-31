# План реализации: управление клубами в SPA

**Ветка**: `007-club-spa-management` | **Дата**: 2026-08-31 | **Спецификация**: [spec.md](spec.md)

**Входные данные**: спецификация фичи из `specs/007-club-spa-management/spec.md`

## Краткое описание

Перенести список, создание, редактирование и просмотр клубов в существующее Vue SPA. Добавить
версионированные V1 JSON-операции для постраничных клубов, одного клуба, создания/обновления и
постраничных персонов клуба. Новые read paths возвращают компактные DTO и используют тот же
`Slice`/pagination-header/serialization-group pattern, что соревнования. Существующий
`ViewClubDto` очищается от неиспользуемого API-поля `normalizeName`; отдельный legacy DTO для клуба
не создаётся. Богатый DTO персоны и непагинированные use cases переименовываются в `Legacy*` и
сохраняются для продолжающих работать Blade, console и legacy API consumers. После перевода ссылок
Blade-страницы клубов удаляются, а их GET-адреса остаются только постоянными редиректами в SPA.

## Технический контекст

**Языки/версии**: PHP 8.5; TypeScript 5.7; Vue 3.5

**Основные зависимости**: Laravel 13, Eloquent, Sanctum 4, Pagerfanta 4, Vue Router 4, Pinia 2,
Axios, PrimeVue 4, Vite 6

**Хранилище**: существующая MySQL 8.4; новые таблицы и миграции не требуются

**Тестирование**: PHPUnit 13 request/unit tests; Vitest 3 + Vue Test Utils; ESLint, Prettier,
vue-tsc; PHPStan/Larastan; php-cs-fixer; Rector

**Целевая платформа**: Linux web application, Laravel API + Vue SPA под `/app/*`, сосуществующие с
legacy Blade

**Тип проекта**: монолитное web-приложение с отдельной SPA-сборкой и версионированным JSON API

**Цели производительности**: default page size 20, `perPage` 1–100; постоянное число SQL-запросов
на страницу независимо от числа строк; отсутствие N+1 и неограниченных новых выборок

**Ограничения**: публичные ответы физически не содержат `created`/`updated`; только активные клубы
и персоны; стабильная сортировка; camelCase V1; даты в UI `YYYY-MM-DD`; legacy person pages и
используемые `/api/person*` сохраняются

**Объём**: четыре новых SPA-маршрута, пять V1 API-операций, три compatibility GET redirect,
удаление трёх Blade-страниц и только подтверждённо мёртвых связанных артефактов

## Проверка конституции

*GATE: проверено до Phase 0 и повторно после Phase 1.*

| Принцип/гейт | Статус | Решение |
|---|---:|---|
| Application / Domain / Bridge / Infrastructure | ✅ | V1 actions и redirects — Bridge; DTO, commands и use cases — Application; правила имени — Domain; Eloquent pagination — Infrastructure. |
| Не расширять legacy `app/Services` | ✅ | Новые use cases создаются только в `app/Application/Service`; существующие legacy consumers получают явно названные `Legacy*` Application adapters. |
| Без фасадов и с DI | ✅ | Production-код получает repository/updater/clock/transaction/serializer через конструктор; новые facade-вызовы не добавляются. |
| Unit + request/frontend tests | ✅ | Контракты list/view/create/update, auth groups, redirects, пагинация, сортировка, duplicate и N+1 покрываются тестами. |
| Целевая архитектура важнее legacy patch | ✅ | V1 не вызывает Blade actions и старые API; старые богатые projections изолируются и не протекают в новый контракт. |
| Только актуальный стек | ✅ | Используются уже установленные Laravel 13/Vue 3/PrimeVue/Vitest/Pagerfanta; новых зависимостей нет. |
| Импорты вместо inline FQCN | ✅ | Все новые PHP-имена подключаются через `use`. |
| CS / STAN / Rector / tests | ✅ | В quickstart закреплены focused checks и полный Definition of Done. |
| N+1 и bounded reads | ✅ | Club count строится агрегатом только по active persons; club/person list paths используют `Slice`; query count проверяется для одной и нескольких строк. |

Существующие `Club` и `Person` являются Eloquent-моделями в `Domain`, а repository interfaces
возвращают framework collection в legacy methods. Это предсуществующий долг. Фича не добавляет
новые framework-зависимости в Domain и не расширяет этот долг: новый paginated путь повторяет уже
принятый `Slice` pattern, а полный перенос моделей выходит за границы фичи.

**Post-design re-check**: все артефакты Phase 1 соблюдают гейты. Новых пакетов, миграций,
`app/Services`, legacy repositories или архитектурных исключений не требуется.

## Структура проекта

### Документация фичи

```text
specs/007-club-spa-management/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── api-clubs.md
│   ├── api-persons.md
│   └── ui-navigation.md
└── tasks.md                 # создаётся $speckit-tasks, не этой командой
```

### Исходный код

```text
app/
├── Application/
│   ├── Dto/{Club,Person}/
│   └── Service/{Club,Person}/
├── Bridge/Laravel/
│   ├── Http/Controllers/Api/V1/{Club,Person}/
│   ├── Http/Serialization/
│   └── Provider/{ApiV1RoutesServiceProvider.php,WebRoutesServiceProvider.php}
├── Domain/{Club,Person}/
└── Infrastructure/Laravel/Eloquent/{Club,Person}/

resources/
├── spa/
│   ├── api/
│   ├── components/
│   ├── pages/clubs/
│   ├── router/
│   └── {i18n.ts,styles.css}
├── lang/by.json
├── views/layouts/navbar.blade.php
└── views/components/club-link.blade.php

tests/
├── Application/Service/{Club,Person}/
├── Domain/Club/
├── Feature/Api/V1/{Club,Person}/
├── Feature/Api/V1/SpaRoutingTest.php
└── Bridge/Laravel/Http/Controllers/Club/
```

**Решение по структуре**: backend остаётся в существующих четырёх слоях, SPA — в
`resources/spa`. V1 actions изолированы от `ApiRoutesServiceProvider` и legacy controllers.
Target и legacy projections персон разделяются именами. Для клубов сохраняется один очищенный
`ViewClubDto`; legacy-код, которому нужен нормализованный ключ, вычисляет его из `name`. Оба пути
используют существующие repository interfaces и assemblers, чтобы не создавать параллельный слой
хранения.

## Phase 0: исследование

Решения и отвергнутые альтернативы зафиксированы в [research.md](research.md). Основные результаты:

- direct JSON + `X-Pagination-*`, optional Sanctum и serialization groups повторяют V1 соревнований;
- target paginated use cases получают канонические имена, старые array/rich paths — `Legacy*`;
- нормализация и проверка дубликатов едины для create/update;
- filter/debounce/pagination/error helpers выносятся из competition-only модуля для повторного
  использования клубами;
- старые GET URL становятся 301 redirect, POST store и Blade actions удаляются;
- legacy `/api/person` и `/api/persons`, person detail и используемые club list consumers остаются.

## Phase 1: проектирование

- [data-model.md](data-model.md) описывает существующие модели, очищенный club DTO,
  target/legacy person DTO, commands, пагинацию и SPA state.
- [api-clubs.md](contracts/api-clubs.md) фиксирует list/view/create/update, auth groups, validation и
  ошибки.
- [api-persons.md](contracts/api-persons.md) фиксирует compact `ViewPersonDto`, обязательный
  `clubId`, active-only и стабильную пагинацию.
- [ui-navigation.md](contracts/ui-navigation.md) фиксирует SPA routes, auth guards, legacy person
  links, navbar и compatibility redirects.
- [quickstart.md](quickstart.md) описывает end-to-end и automated validation.
