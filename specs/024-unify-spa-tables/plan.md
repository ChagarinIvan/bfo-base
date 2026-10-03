# План реализации: единые SPA-таблицы и навигация результатов

**Ветка**: `024-unify-spa-tables` | **Дата**: 2026-09-13 | **Спека**: [spec.md](spec.md)

## Резюме

Создать единый SPA layout листинга, который хранит конфигурацию видимых столбцов в browser
storage по стабильному ключу таблицы и состоянию авторизации, показывает selector до optional
sticky filter slot и предоставляет scoped slot для условного рендера колонок. Перевести все
текущие SPA `DataTable`-листинги на layout. Вынести построение URL строки протокола в общий
helper, применить его во всех контекстах, добавить защищённый API use case и кнопку пересчёта
разрядов персоны.

## Технический контекст

**Язык/версия**: TypeScript/Vue 3/PrimeVue, PHP 8.5/Laravel 13.  
**Основные зависимости**: Vue composition API, PrimeVue DataTable, Pinia, Laravel API actions.  
**Хранение**: browser local storage для предпочтений; MySQL-модель персон для пересчёта не меняется.  
**Тестирование**: Vitest/Vue Test Utils, PHPUnit API request и Application unit tests.  
**Целевая платформа**: браузер и Laravel HTTP runtime.  
**Тип проекта**: SPA внутри Laravel-монолита.  
**Ограничения**: без новой БД-схемы, без дополнительных запросов при смене колонок, без новых N+1.  
**Масштаб**: все текущие SPA DataTable-листинги, один новый защищённый person use case.

## Проверка конституции

| Gate | Статус | Обоснование |
|------|--------|-------------|
| Слоистость | Pass | HTTP action создаёт command; существующий Application use case выполняет пересчёт. SPA не получает domain object. |
| Новые persistence ports | Pass | Хранение настроек локально; БД/репозитории не затрагиваются. |
| Тесты | Pass | Поведение layout, URL и API покрывается целевыми тестами; Application unit тестирует command. |
| Производительность | Pass | Выбор колонок не инициирует запросы; фильтры не меняют query semantics; новых DB-query paths нет. |

## Структура проекта

```text
resources/spa/
├── components/ListingTable.vue       # selector, storage and optional sticky filters
├── components/tableModels.ts          # stable column keys and protocol-line URL helper
├── pages/**                           # existing list pages migrated to layout
└── api/persons.ts                     # manual rank rebuild request

app/
├── Application/Service/Person/        # existing RebuildPersonRanks command/service
└── Bridge/Laravel/Http/Controllers/Api/V1/Person/
    └── RebuildPersonRanksAction.php
```

**Решение по структуре**: общий layout находится рядом с существующими SPA-компонентами;
ручной пересчёт повторно использует целевой Application service и добавляет только Bridge adapter.

## Закрепление заголовков таблиц, 2026-10-03

`ListingTable` получает общий корневой контейнер и DOM refs панели и контейнера. Composable
`useStickyTableHeaders` смещает существующий `thead` внутри собственного DataTable при
вертикальной прокрутке страницы. Смещение ограничено границами таблицы и нижним краем панели.
PrimeVue table container сохраняет `overflow: auto`, поэтому горизонтальная прокрутка и
выравнивание ячеек не меняются. Используется исходный header с его slots и интерактивными
контролами, без дубликатов DOM и отдельных копий таблицы.

Scroll/resize обрабатываются через один animation frame. ResizeObserver учитывает перенос
панели и размеры layout; MutationObserver учитывает асинхронное появление таблиц и смену
колонок в default slot. При unmount observers/listeners/frame удаляются. Отступ перехода к
строке протокола включает высоту панели и заголовка. CSS использует переменные темы.

Проверки покрывают изменение высоты панели, ограничение смещения концом таблицы,
default-slot DataTable, сохранение header slot и освобождение observers при unmount.
Frontend CI прошёл. Визуальная проверка вертикальной/горизонтальной прокрутки и тем
не выполнена: браузеры CUA недоступны. Геометрия в компонентных тестах моделируется DOMRect.
