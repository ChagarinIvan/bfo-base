# Data model

## ProtocolLine

| Поле | Тип | Правило |
| --- | --- | --- |
| created_at | dateTime | Обязательно, после создания сохраняется |
| created_by | integer | Автор создания |
| updated_at | dateTime | Обязательно, время последнего существующего перехода |
| updated_by | integer | Автор этого перехода |

created и updated читаются как Impression через существующий cast.
Автоматические Eloquent timestamps отключены. Defaults, подменяющие аудит, не нужны.
Источник миграции: protocol_lines.distance_id -> distances.event_id -> events.

## Переходы

- Импорт: created = updated = Impression(Clock.now(), userId).
- Объединение событий: created = updated = newEvent.created, полученный через Clock фабрики Event.
- Историческое заполнение: created = Event.created, updated = Event.updated.
- assignPerson/setPerson/activateRank: created сохраняется, updated = аргумент операции.
- Отклонённый переход: состояние и аудит сохраняются.
- Повторный save: аудит сохраняется, pending event не отправляется повторно.
- Очистка протокола: строка и её аудит физически удаляются по действующим правилам.
