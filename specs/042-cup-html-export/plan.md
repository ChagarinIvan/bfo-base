# Implementation Plan: Экспорт таблицы кубка в CSV и HTML

**Branch**: `master` | **Date**: 2026-10-07 | **Spec**: [spec.md](spec.md)

**Input**: Восстановить HTML-экспорт полного кубка и заменить одиночную кнопку экспорта меню CSV/HTML.

## Summary

Сохранить один `ExportCupTableService` и `CupExportAssembler` как источник DTO. Добавить HTML-сериализацию DTO и один сборщик HTTP-ответа для CSV/HTML на Bridge-границе, выбор `format` на существующем V1 маршруте и два пункта выпадающего меню в карточке кубка. Необязательный `groupId` ограничивает построение DTO одной группой. Без него остаётся полный экспорт. HTML использует `counted` для жирного выделения этапов. Кнопка показывает состояние запроса, а ночные цвета ячеек заданы отдельно. Старый Blade-шаблон рассчитывал очки отдельно и не подходит для восстановления.

## Technical Context

**Language/Version**: PHP 8.5 / Laravel 13; TypeScript / Vue 3  
**Primary Dependencies**: текущие `CupTableBuilder`, `ExportCupTableService`, PrimeVue и Axios  
**Storage**: без новых таблиц и файлов на сервере  
**Testing**: PHPUnit request/unit, Vitest component/API  
**Target Platform**: SPA и V1 API  
**Project Type**: web application  
**Performance Goals**: один расчёт полного DTO на запрос, без запроса на группу и без новых N+1  
**Constraints**: сохранить CSV и авторизацию; HTML в UTF-8, с экранированием текста  
**Scale/Scope**: один экспортный маршрут, одна карточка кубка, одна новая сериализация

## Constitution Check

- Read-only export не меняет агрегаты, поэтому command на мутацию, `Impression`, события, транзакция и политика удаления не применяются.
- Существующий Application service принимает `ExportCupTable` и получает полную таблицу через Domain repositories и `CupTableBuilder`. Новый сериализатор остаётся в Bridge.
- V1 action формирует command, возвращает attachment как документированное исключение из JSON DTO контракта. Ошибки остаются в стандартном JSON-виде.
- UI сохраняет белорусские подписи и использует PrimeVue для доступного меню.
- Изменение поведения покрывается request и компонентными тестами. Проверка после проектирования: нарушений конституции нет.

## Project Structure

### Documentation

```text
specs/042-cup-html-export/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/export.md
├── quickstart.md
├── checklists/requirements.md
└── tasks.md
```

### Source Code

```text
app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/ExportCupTableAction.php
app/Bridge/Laravel/Http/Serialization/CupTableHtmlSerializer.php
app/Bridge/Laravel/Http/Serialization/CupTableExportResponseAssembler.php
app/Bridge/Laravel/Http/Controllers/ApiAction.php
resources/spa/api/cups.ts
resources/spa/components/CupInfoNavigation.vue
resources/lang/by.json
resources/spa/pages/updates/updates.ts
.specify/memory/backend-architecture-manifest.md
tests/Feature/Api/V1/Cup/ExportCupTableActionTest.php
tests/Bridge/Laravel/Http/Serialization/CupTableHtmlSerializerTest.php
resources/spa/api/cups.test.ts
resources/spa/components/CupInfoNavigation.test.ts
```

**Structure Decision**: Обе сериализации получают неизменённый `ExportCupTableDto`; расчёт не переносится в HTTP или Vue.
