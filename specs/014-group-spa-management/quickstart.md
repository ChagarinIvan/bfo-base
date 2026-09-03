# Quickstart: управление группами в SPA

## Public list/detail

1. Открыть `/app/groups` без сессии.
2. Убедиться, что таблица пагинирована, отсортирована по `distancesCount DESC, id ASC`, а
   `created`/`updated` не видны.
3. Ввести `A`, дождаться debounce около 300 мс и проверить filtered list; очистить input и
   убедиться, что вернулся полный список.
4. Открыть `/app/groups/{id}` и проверить info table, starts table и paginator.
5. Применить competition name, year и exact date filters совместно; проверить empty state.

## Auth mutations

1. Войти и открыть `/app/groups/{id}/edit`.
2. Сохранить новое уникальное имя; проверить redirect на detail и видимые impressions.
3. Отправить duplicate/empty/too-long name; проверить 409/422 и отсутствие изменения.
4. Открыть `/app/groups/{id}/merge`, найти target через search/pagination, подтвердить merge и
   проверить перенос distances и исчезновение source.
5. Открыть delete dialog, отменить, затем подтвердить; проверить удаление group-owned data.

## Legacy safety

1. Проверить отсутствие group-only web routes `/groups`, `/groups/{id}`, `/groups/{id}/unit`.
2. Проверить navbar и все результаты `rg '/groups|ShowGroup|GroupsService|GroupsRepository'`.
3. Проверить кубки, parser и другие shared consumers после удаления group-only Blade actions.

## Quality gates

В конце фичи выполнить узкие изменённые тесты, затем один раз:

`composer cs`
`composer stan`
`composer rector`
`composer test`
`npm run ci`
`git diff --check`

### Последний запуск

- `npm run ci` — успешно: 29 файлов / 77 frontend-тестов, lint, typecheck и production build.
- `composer cs`, `composer stan`, `composer rector` — успешно.
- `ParserServiceTest` — успешно.
- PHP request-тесты и полный `composer test` требуют отдельной чистой MySQL test-схемы: в текущем
  окружении несколько одновременно начатых `migrate:fresh` прогонов конкурировали за `bfo_base`
  (`migrations/protocol_lines already exists`). Их результат не является сигналом ошибки фичи.
