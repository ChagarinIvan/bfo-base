# API-контракт: годы V1

## GET /api/v1/years

Публичный endpoint со всеми доступными годами из App.Models.Year::cases().
Авторизация, query-параметры, пагинация и входной DTO отсутствуют.

### Response 200 OK

    [2026, 2025, 2024]

Ответ сериализуется как прямой JSON-массив без поля data.
Application use case не принимает command и не обращается к Eloquent или Request.
