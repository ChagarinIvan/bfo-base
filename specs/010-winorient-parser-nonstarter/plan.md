# План реализации: WinOrient без старта

**Ветка**: `010-winorient-parser-nonstarter` | **Дата**: 2026-09-03 | **Спека**: `spec.md`

## Резюме

Добавить regression-тест на реальный WinOrient fixture и изменить обработку значения
`н.старт` в `WinOrientHtmlParser`, чтобы right-to-left cursor потреблял только поле
результата и не терял год рождения.

## Технический контекст

- **Язык/стек**: PHP 8.5, Laravel 13, PHPUnit 13.
- **Источник**: `storage/tests/2026/20260725.htm`, WinOrient HTML.
- **Парсер**: `app/Models/Parser/WinOrientHtmlParser.php`.
- **Тест**: `tests/Models/Parser/WinOrientParserTest.php` и общий `AbstractParser`.
- **Подход**: TDD red → минимальная правка → green; старые fixtures не удаляются.

## Проверка конституции

Изменение ограничено существующим parser/test слоями, не добавляет legacy-сервисов,
не меняет доменные контракты и покрывается автоматическим тестом.

## Этапы

1. Зафиксировать ожидаемую строку fixture в существующем тесте и увидеть red.
2. Подтвердить смещение cursor и добавить `н.старт` к безрезультатным значениям времени.
3. Запустить полный набор parser-тестов и проверить diff/форматирование.
4. Создать и провалидировать skill `parser-maintenance`.
