# Implementation Plan: Экспорт разрядов и таблиц кубка

**Branch**: `master` | **Date**: 2026-10-09 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/043-person-rank-csv-cup-xlsx/spec.md`

## Summary

Добавить авторизованный потоковый CSV персон по фильтрам списка. Заменить CSV кубка на XLSX средствами уже установленного PhpSpreadsheet, сохранить HTML и общий источник данных. Групповой экспорт исключает нулевые итоги.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript, Vue 3
**Primary Dependencies**: Laravel 13, PhpSpreadsheet 5, PrimeVue 4
**Storage**: MySQL 8.4; экспорт не меняет данные
**Testing**: PHPUnit 13, Vitest 3, PHPStan, PHP CS Fixer, Rector
**Target Platform**: API V1 и SPA, браузер с поддержкой скачивания Blob
**Project Type**: Web application
**Performance Goals**: CSV персон читает записи порциями; экспорт кубка повторно использует кэш таблиц
**Constraints**: UTF-8, допустимые имена листов Excel, доступ только по Bearer token
**Scale/Scope**: Один маршрут персон; существующий маршрут кубка; два места запуска в SPA

## Constitution Check

- Новый сценарий персон: Bridge action создаёт command, Application service получает `Criteria` через command и вызывает `PersonRepository::exportByCriteria()`. `EloquentPersonRepository` строит общий запрос для списка и экспорта, а строки экспорта читает порциями. Bridge-сериализатор защищает ячейки CSV, похожие на формулы Excel.
- Экспорт кубка: существующие Application service и общий DTO остаются источником данных. Сервис передаёт билдеру таблицы флаг исключения нулевых итогов только для выбранной группы. Кэш использует отдельный ключ для отфильтрованной таблицы. XLSX и HTML сериализуются на Bridge boundary.
- Запись данных, мутация агрегата, событие, транзакция и политика удаления не применяются. N+1 исключается выборкой только нужных полей CSV и существующим кэшем таблиц кубка.
- API request-тесты имеют `@see` на action. Поведение покрывают PHP и SPA тесты.

## Project Structure

### Documentation (this feature)

```text
specs/043-person-rank-csv-cup-xlsx/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/export.md
├── quickstart.md
├── checklists/requirements.md
└── tasks.md
```

### Source Code (repository root)

```text
app/Application/Service/Person/                 # command и сервис экспорта
app/Domain/Person/                              # порт и строка экспорта
app/Infrastructure/Laravel/Eloquent/Person/     # фильтрованное чтение
app/Bridge/Laravel/Http/Controllers/Api/V1/     # API action
app/Bridge/Laravel/Http/Serialization/          # CSV и XLSX
resources/spa/api/                             # запросы файлов
resources/spa/pages/persons/                   # кнопка CSV
resources/spa/components/                      # меню кубка
tests/Feature/Api/V1/                          # request-тесты
```

**Structure Decision**: Следовать существующим слоям репозитория и повторно использовать расчёт кубка.

## Post-design Constitution Check

План не расширяет legacy каталоги и не вводит новых мутаций. Новая порционная выборка персон реализуется через `PersonRepository`; транспортные DTO не покидают command.
