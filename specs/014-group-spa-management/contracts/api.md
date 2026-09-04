# API-контракты: управление группами в SPA

Все пути ниже находятся под `/api/v1` и используют существующий JSON serializer и pagination
headers (фактические project names для `X-Current-Page`, `X-Per-Page`, `X-Total`, `X-Last-Page`).

## List groups

`GET /groups?name={string}&page={int}&perPage={int}`

- Auth: public; optional authentication only changes projection.
- `200`: array of `ViewGroupDto`.
- Public item: `{ id, name, distancesCount }`.
- Authenticated item additionally has `{ created, updated }`, assembled from group audit columns.
- Order: `distancesCount DESC, id ASC`.
- Search uses normalized `name` against `normalize_name`; invalid `page` or `perPage`: `422` with
  field errors. Group search accepts one significant character; event competition-name search accepts
  at least three characters.

## View group

`GET /groups/{groupId}`

- Auth: public.
- `200`: one `ViewGroupDto`; `404` for missing group.
- `created`/`updated` are auth-only.

## List group starts

`GET /events?groupId={int}&withCompetition=1&competitionName={string}&year={YYYY}&date={YYYY-MM-DD}&page={int}&perPage={int}`

Если передан `competitionName`, он должен содержать не менее трёх символов.

- Auth: public; existing event criteria remain compatible.
- `200`: paginated event/start array with competition name when `withCompetition=1`.
- Combines all supplied filters; only current page is loaded.
- Invalid criteria: `422`; stable ordering is explicit and documented by implementation.

## Update group

`PUT /groups/{groupId}` with `{ "name": "..." }`

- Auth: required.
- `200`: updated `ViewGroupDto`; `401` unauthenticated; `404` missing; `422` field validation;
  `409` `{ "errors": [{ "code": "group_name_already_exists", "message": "..." }] }`.

## Delete group

`DELETE /groups/{groupId}`

- Auth: required.
- `204` with no body; `401` unauthenticated, `404` missing.
- Soft-deletes group (`active=false`) atomically; distances and protocol lines сохраняются.

## Merge groups

`POST /groups/{sourceGroupId}/merge` with `{ "targetGroupId": 123 }`

- Auth: required.
- `204` with no body after the target has been updated.
- `401` unauthenticated, `404` source/target missing, `422` invalid payload,
  `409` `cannot_merge_same_group` for equal ids.
- Operation is atomic and idempotently rejects source == target without data changes.
- SPA confirmation displays source and target names; merge actions use the success visual style.
