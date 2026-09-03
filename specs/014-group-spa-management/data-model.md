# Модель данных: управление группами в SPA

## Group

Существующая запись `groups`:

- `id: int` — идентификатор.
- `name: string` — отображаемое название.
- `normalize_name: string NOT NULL` — значение `GroupNameNormalizer` для поиска и duplicate-check.
- `active: bool = true` — признак soft-delete; inactive rows исключаются из read models.
- `distancesCount: int` — вычисляемый count связанных `distances`, не отдельное поле.
- `created: ImpressionDto`, `updated: ImpressionDto` — auth-only projection, хранимый через
  `created_at/created_by/updated_at/updated_by`; DB defaults — `CURRENT_TIMESTAMP` и system user
  ID `10`, существующие строки получают `normalize_name` backfill.

Связь: `Group 1 — N Distance` через `distances.group_id`.

## Event/Start projection

Для `GET /api/v1/events?groupId=...&withCompetition=1`:

- `id: string`
- `competitionId: string`
- `competitionName: string` — включается при `withCompetition=1`, иначе отсутствует/не меняет
  существующий response contract.
- `name: string` — название старта/event.
- `date: YYYY-MM-DD`
- `participantsCount: int` — server-side protocol line count.
- `created`, `updated` — auth-only как в существующем `ViewEventDto`.

`groupId` выбирает active events, у которых существует distance с этим group id. Если у события
несколько distance этой группы, событие возвращается один раз.

## Search criteria

### GroupSearchCriteria

- optional `name: string`, trim, min 1 значимый символ;
- `page: positive int`;
- `perPage: positive int` из существующего pagination contract;
- fixed sorting `distancesCount DESC, id ASC`.

### EventSearchCriteria

Сохраняет существующие criteria и добавляет:

- optional `groupId: positive int`;
- optional `withCompetition: boolean`;
- optional `competitionName: string`, trim, min 3 если передан;
- optional `year: four-digit int`;
- optional `date: YYYY-MM-DD`;
- optional `page`/`perPage`.

## Mutation invariants

- Group name не пустой после trim и не длиннее 255.
- Нормализованные имена не дублируются; update не конфликтует с самой собой.
- Delete soft-deletes group (`active=false`) atomically; distances and protocol lines сохраняются.
- Merge source and target must exist and differ; all source distances point to target before source is
  soft-deleted; no partial state is committed.
- Public serializer never exposes `created`/`updated`.
