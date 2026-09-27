# Модель данных: таблица кубка

## CupResultRow Slice

API возвращает Slice строк, отфильтрованных по имени и выбранной странице.
Метаданные кубка берутся из cup endpoint, заголовки этапов — из списка этапов.

## CupTableRow

| Поле | Назначение |
|---|---|
| `place` | Итоговое место |
| `personId`, `personName`, `personYear`, `clubId`, `clubName` | Данные спортсмена |
| `stages` | Map по `stageId` в порядке контракта |
| `totalPoints` | Сумма зачётных очков |
| `averagePoints` | Среднее по зачётным этапам |

## CupTableStageCell

| Поле | Назначение |
|---|---|
| `points` | Отображаемые очки или null |
| `counted` | Входит ли значение в сумму/среднее |
| `stageId` | ID этапа, к которому относится ячейка |
| `distanceId`, `protocolLineId` | Данные результата для SPA-ссылки на протокол; `eventId` берётся из этапа |

Новая persistence model не создаётся. Scoring semantics остаются в доменном типе
кубка и проверяются против legacy output.
