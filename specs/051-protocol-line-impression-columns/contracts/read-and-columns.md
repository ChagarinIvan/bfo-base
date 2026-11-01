# Read and columns contract

## HTTP

GET /api/v1/protocol-lines с текущими camelCase personId/distanceId/withEvent/
withCompetition/perPage/page и GET /api/v1/cup-events/{id}/points с groupId/name/
perPage/page сохраняют порядок, фильтрацию, status и pagination headers.
Каждый объект дополнительно содержит только при Bearer authentication:

```json
{
  "created": { "at": "2026-10-27T10:00:00+00:00", "by": "7" },
  "updated": { "at": "2026-11-01T12:30:00+00:00", "by": "9" }
}
```

Для guest оба ключа отсутствуют, не null и не маскированные значения.
Для points данные принадлежат point.protocolLine. Дополнительных запросов аудита
нет; атрибуты загружены вместе со строками.

## UI

| Страница | TableId | Колонки | Defaults |
| --- | --- | --- | --- |
| Событие | event-protocol-lines | created, updated | false, false |
| Спортсмен | person-participation | created, updated | false, false |
| Этап кубка | cup-event-points | created, updated | false, false |

Колонки и их chooser entries доступны только при auth. Labels существующие
spa.competitions.created/updated, ячейки ImpressionDetails с users list.
Переключение каждой независимо, сохранение через ListingTable. Сброс через
очистку ключа и remount возвращает defaults. Logout скрывает private ячейки даже
при сохранённых created/updated в authenticated namespace.
Ошибка users не мешает загрузке результатов; unknown author и missing Impression
используют существующие fallback. Гость не вызывает users API.
