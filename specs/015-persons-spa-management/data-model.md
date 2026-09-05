# Модель данных: SPA-управление персональными промптами

## PersonPrompt

Существующая запись `persons_prompt`:

- `id: int` — идентификатор промпта.
- `person_id: int` — ссылка ровно на одну активную персону.
- `prompt: string` — обязательное значение длиной до 255 символов по текущему request contract.
- `metaphone: string` — рассчитанное phonetic-представление, возвращаемое в read projection.
- `active: bool` — признак доступности; delete выполняет soft delete (`false`). Старые записи миграция помечает `true`.
- `created`, `updated` — существующие auth-only impression projections.

Связь: `Person 1 — N PersonPrompt`; inactive/unknown person не является публичным владельцем списка.

## Search criteria

`PersonPromptSearchCriteria`/существующий criteria payload:

- required positive `personId` из route context;
- optional `activePerson`, default `true`;
- positive `page` и `perPage` из общего pagination contract.

Сортировка: `persons_prompt.id DESC` как стабильный текущий порядок, ограниченный текущей страницей.

## Mutation invariants

- `prompt` не пустой и не длиннее 255 символов после применения существующего request contract.
- При create/update metaphone рассчитывается существующим domain updater/factory.
- Delete выполняется только для существующего активного prompt, устанавливает `active=false` и публикует доменное событие.
- Update публикует `PersonPromptUpdated`; soft delete публикует `PersonPromptDisabled`.
- API list/view/mutation routes требуют текущую API authentication middleware; application unit tests не создают
  Eloquent entities.
