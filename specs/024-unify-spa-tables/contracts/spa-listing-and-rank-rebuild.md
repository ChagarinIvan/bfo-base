# Контракты listing layout и пересчёта разрядов

## SPA listing layout

- принимает стабильный `table-id`, описание разрешённых колонок и optional slot фильтров;
- отдаёт scoped `isVisible(columnKey)` для колонок листинга;
- сохраняет только допустимые, непустые `visibleColumnKeys` под ключом конкретной таблицы и
  access variant;
- если filter slot передан, его контейнер использует sticky positioning.

## `POST /api/v1/persons/{personId}/ranks/rebuild`

Требует стандартную API-аутентификацию.

| Ответ | Значение |
|-------|----------|
| `204 No Content` | Разряды указанного существующего спортсмена пересчитаны. |
| `401 Unauthorized` | Нет пользователя API. |
| `404 Not Found` | Спортсмен не существует или недоступен. |

Action получает `personId` из маршрута и `UserId` auth context, создаёт application command;
никаких transport DTO в application service не передаётся.
