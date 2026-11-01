# Data model

## ProtocolLine

Существующие created и updated типа Domain Auth Impression. Каждый содержит
Carbon at и user identifier by. Источник аудита только сама строка;
дата соревнования/события и аудит CupEventPoint не подставляются.
Схема, обязательность хранения и жизненный цикл из 050 сохраняются.

## Read DTO

ViewProtocolLineDto и ViewCupEventPointDto получают created/updated ImpressionDto.
Каждый содержит at, строку ISO 8601, и by, строковый ID. Serializer исключает
оба поля для гостей. Для authenticated данные обязательны в актуальном ответе.
Frontend interfaces имеют optional created/updated для guest omission; компонент
поддерживает отсутствующий Impression, выводя стандартное пустое значение.

## Table preferences

Ключ bfo.table.<tableId>.<authenticated|guest>, значение JSON-массив ключей.
TableId: event-protocol-lines, person-participation, cup-event-points.
Создание/изменение обозначаются стабильными ключами created/updated, defaults false.
Старые настройки не содержат новых ключей и сохраняются без автоматического
добавления. Для cup-event-points прежний guest key является источником первичного
переноса в authenticated key, если destination отсутствует. Guest key не удаляется.

## State transitions

Новых доменных переходов нет. Выбор колонок меняет только localStorage.
Смена auth убирает private columns и переключает namespace ListingTable.
