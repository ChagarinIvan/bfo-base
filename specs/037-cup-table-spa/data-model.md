# Модель данных: таблица кубка

## CupTableView

| Поле | Назначение |
|---|---|
| `cupId`, `groupId` | Контекст таблицы |
| `stages[]` | Стабильный порядок этапов: `stageId` (ID этапа кубка), дата, name и `eventId` |
| `rows[]` | Пагинированные спортсмены |
| pagination headers | `currentPage`, `perPage`, `hasNext` |

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
