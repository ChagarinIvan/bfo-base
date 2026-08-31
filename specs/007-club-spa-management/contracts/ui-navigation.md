# UI-контракт: SPA clubs и миграция ссылок

## SPA routes

| Route | Доступ | Экран |
|---|---|---|
| `/app/clubs` | Public | Постраничный список и filter по названию. |
| `/app/clubs/create` | Authenticated | Общая ClubForm в режиме создания. |
| `/app/clubs/{id}` | Public | Club details + paginated active persons. |
| `/app/clubs/{id}/edit` | Authenticated | Предзаполненная ClubForm. |

Guard неавторизованного create/edit отправляет на `/app/login?return={fullPath}`. После create и
update SPA открывает `/app/clubs/{id}`. Club list/detail показывают impressions/actions только при
аутентификации.

## Interaction rules

- Единственный filter списка клубов — name: trim, minimum 3, debounce 300 ms.
- Один-два символа дают локальную подсказку и не отправляются; API 422 показывается под name.
- Изменение/очистка filter сбрасывает page=1; последний запрос выигрывает race.
- Loading, empty, not-found и general error различаются.
- Club form одна для create/edit; duplicate и validation остаются у поля name.
- Person name ведёт обычным `href` на `/persons/{id}/show`, не в Vue Router.
- Person table не добавляет filter в этой фиче; она использует server pagination.
- Impressions используют общий `ImpressionDetails`; даты не локализуют названия месяцев.
- Club action содержит только edit; delete вне scope.

## Navbar и внутренние ссылки

- SPA navbar: Clubs item становится `{ href: '/app/clubs', spa: true }`.
- Legacy Blade navbar: Clubs → `/app/clubs`, Competitions → `/app/competitions`.
- Ссылки клуба в events/cups/person legacy UI → `/app/clubs/{id}`.
- Остальные navbar destinations и глубокие person pages остаются legacy.

## Compatibility redirects

| Старый GET | Ответ | Destination |
|---|---:|---|
| `/clubs` | 301 | `/app/clubs` |
| `/clubs/create` | 301 | `/app/clubs/create` |
| `/clubs/{clubId}/show` | 301 | `/app/clubs/{clubId}` |

Redirect routes не рендерят Blade и не выполняют старую auth/business logic. Старый
`POST /clubs/store` удаляется и не перенаправляет mutation.

## Cleanup boundary

Удаляются club index/create/show Blade views, их actions/tests, `<x-club-link>` PHP registration и
подтверждённо неиспользуемые `DisableClub*`/переводы. Blade partial
`components/club-link.blade.php` сохраняется, пока используется через `@include`, но его href
становится SPA. Legacy club/person list services, forms, console commands, `/api/person` и
`/api/persons` сохраняются под `Legacy*` именами.

