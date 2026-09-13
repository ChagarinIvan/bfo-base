# Исследование: единые SPA-таблицы

## Решение: scoped listing layout, а не универсальная схема данных

**Решение**: `ListingTable` владеет selector-ом колонок, ключом local storage и sticky filter
area, а каждая страница передаёт перечень колонок и рендерит свои специализированные ячейки
через scoped slot `isVisible`.

**Обоснование**: существующие листинги имеют разные типы строк, ссылки и action menus. Единый
schema-driven renderer вынудил бы переносить page-specific view logic в общий компонент.
Layout обеспечивает одинаковый UX, но не размывает типы и ответственность страниц.

**Альтернативы**:

- Хранить предпочтения на сервере — отклонено: нет требования синхронизации устройств и это
  добавляет миграцию/API/авторизационную модель.
- Встроить selector в каждый листинг — отклонено: вернёт разрозненные storage keys,
  accessibility и sticky behavior.

## Решение: отделять storage ключом таблицы и access variant

**Решение**: storage key включает стабильный table id и вариант `guest`/`authenticated`; при
загрузке остаются только доступные текущему варианту ключи, а пустой/повреждённый выбор
заменяется стандартным набором.

**Обоснование**: служебные колонки не могут появиться у гостя после logout, а настройки разных
листингов не пересекаются.

## Решение: канонический hash `#protocol-line-{id}`

**Решение**: единый URL helper создаёт `/app/events/{eventId}?distanceId={distanceId}#protocol-line-{lineId}`;
distance parameter добавляется, когда он известен.

**Обоснование**: event view уже принимает старый и prefixed hash, но prefixed id не конфликтует
с иными element ids и выражает тип якоря. Правильная дистанция выбирается до прокрутки.

## Решение: синхронный reuse существующего rank rebuild use case

**Решение**: auth-only action строит `RebuildPersonRanks(personId, userId)` и вызывает существующий
`RebuildPersonRanksService`; SPA ждёт успешный ответ и повторно читает history/person context.

**Обоснование**: точечный пересчёт уже transactional и используется другими application flows;
новая очередь не улучшит этот интерактивный путь.
