# Cup Event Context API

`GET /api/v1/cup-events?eventIds[]=12&eventIds[]=18`

Public, non-paginated compact list. `eventIds` is required, non-empty and each
value is a positive integer.

```json
[{"eventId":"12","cupEventId":"7","cupId":"3","cupName":"Кубак Беларусі","cupType":"master","href":"/cups/3/7/M21/show"}]
```

Only active cup stages belonging to active cups are returned. An unmatched ID
is omitted. The response can contain multiple contexts for one event ID.
