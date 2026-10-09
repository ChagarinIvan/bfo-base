# Export contracts

## Разряды персон

`GET /api/v1/persons/export` требует Bearer token и принимает `name`, `clubId`, `rankId`, `birthYear` с теми же правилами, что `GET /api/v1/persons`. `page` и `perPage` не ограничивают файл.

Успех: `text/csv; charset=UTF-8`, attachment `persons-ranks.csv`, UTF-8 BOM, разделитель `;`, CRLF. Заголовок: `lastname;firstname;birthday;rank`. `birthday` равен `YYYY` или пуст. Сортировка: фамилия, имя, ID. Пустая выборка содержит только заголовок. Гость получает 401; неверный фильтр получает 422 JSON без attachment.

## Таблицы кубка

`GET /api/v1/cups/{cupId}/export` требует Bearer token. `format` принимает `xlsx` или `html`; по умолчанию `xlsx`. `groupId` остаётся необязательным и проверяется по существующему контракту группы.

| Формат | Content-Type | Расширение |
| --- | --- | --- |
| отсутствует / `xlsx` | `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` | `.xlsx` |
| `html` | `text/html; charset=UTF-8` | `.html` |

Заголовки обеих таблиц: `№`, `Прозвішча, Імя`, `Год`, даты этапов `дд.мм`, `Ачкі`, `Сярэдняе`, `Месца`. Столбец клуба отсутствует. Полный XLSX содержит лист для каждой группы в её исходном порядке. Групповой экспорт содержит одну группу и исключает строки с итогом 0. В XLSX ячейки зачётных этапов жирные. HTML сохраняет жирное выделение и экранирует текст.

`format=csv` и другой неверный формат возвращают 422 JSON. Неизвестный кубок возвращает 404 `cup_not_found`; неподдерживаемая группа 400 `cup_group_not_supported`; гость 401. Ошибки не содержат attachment.
