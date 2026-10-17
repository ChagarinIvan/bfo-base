# Implementation Plan: элитные группы «Лосиной сцежки»

**Branch**: `master`, без переключения | **Date**: 2026-10-17 | **Spec**: [spec.md](spec.md)

## Summary

Сохранить прежние шаблоны `ElkPathCup::GROUPS_MAP`; при выборе дистанции использовать критерий `groupNamesIgnoringSpaces`, удаляющий обычные пробелы с обеих сторон LIKE-сравнения. Зафиксировать исходный XLSX регрессионным интеграционным тестом. Уже загруженные результаты становятся доступны после штатной очистки кэша, без переимпорта и переименования.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript для истории обновлений.

**Primary Dependencies**: Laravel 13, PhpSpreadsheet 5, PHPUnit 13, Vitest.

**Storage**: существующие MySQL groups/distances/protocol_lines/cup_events; Redis cache cups.

**Testing**: integration с реальным XLSX и БД; регрессия прежних aliases и остальных категорий; история обновлений.

**Target Platform**: существующий сервер и SPA.

**Project Type**: Laravel web application.

**Performance Goals**: не увеличивать число SQL-запросов при добавлении спортсменов; без новых relations или полного обхода.

**Constraints**: только ElkPathCup; парсеры, общие нормализаторы и формула очков сохраняются.

**Scale/Scope**: сопоставление без обычных пробелов только для ElkPathCup, 25 элитных строк из 92, штатная очистка кэша.

## Constitution Check

Пройден до исследования и после дизайна. Правило сопоставления принадлежит Domain CupType; новый Application-сценарий, command и endpoint не нужны. Изменение чтения не создаёт перехода агрегата, audit, доменного события, transaction или политики удаления. Существующий порт сохраняется; добавляется явный критерий фильтрации `groupNamesIgnoringSpaces`. Обычный `groupNames` сохраняет прежнее поведение. Новые Laravel-зависимости в Domain и legacy код не добавляются. Integration-тесты создают БД; Application/Domain unit не создаются с Eloquent. Переходные зависимости CupType от Eloquent/legacy из REF-07/REF-13 манифеста не расширяются, их рефакторинг вне задачи. Данные и схема не меняются. Cache сбрасывается существующим Application service. Нужна запись в `/app/updates`.

## Project Structure

```text
specs/048-elk-path-elite-groups/{spec,plan,research,data-model,quickstart,tasks}.md
specs/048-elk-path-elite-groups/contracts/cup-groups.md
app/Domain/Cup/CupType/ElkPathCup.php
app/Infrastructure/Laravel/Eloquent/Distance/EloquentDistanceRepository.php
tests/Fixtures/Protocols/2026/Results_night_cup.xlsx
tests/Feature/Cup/ElkPathCupEliteGroupsTest.php
resources/spa/pages/updates/{updates.ts,updates.test.ts}
```

**Structure Decision**: явное указание режима сопоставления доменным CupType и его реализация в существующем persistence adapter с интеграционной проверкой исходного файла. Не изменять парсер.

## Complexity Tracking

Новых нарушений нет. Существующий технический долг перечислен в Constitution Check.
