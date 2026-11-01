# Research: Колонки аудита строк протокола

## Источники данных

Decision: расширить ViewProtocolLineDto и ViewCupEventPointDto.
Rationale: EventViewPage и PersonViewPage читают /protocol-lines; CupEventViewPage
читает /cup-events/:id/points, CupAssembler уже имеет соответствующий ProtocolLine.
Alternatives: отдельный запрос аудита на строку создаёт N+1; аудит события неверен.
ViewPersonProtocolLineDto не используется текущими тремя экранами.

## Доступ и отображение

Decision: AuthAssembler + Groups(['authenticated']) на обоих новых полях,
существующий ImpressionDetails и белорусские spa.competitions.created/updated.
Rationale: так уже работают impressions сущностей проекта, включая guest omission,
email автора, fallback с ID и подробный popover.
Alternatives: публичная выдача нарушает действующие границы доступа, отдельный
компонент дублирует формат и разрешение автора.

## Настройки

Decision: сохранить ListingTable без изменений. Новые колонки defaultVisible false.
На странице этапа кубка добавить authenticated и однократное копирование прежнего
набора из guest key при отсутствии authenticated key.
Rationale: sanitizeVisibleColumns сохраняет старый разрешённый набор. Ранее этап
кубка всегда сохранял его в guest namespace. Настройки других таблиц независимы.
Alternatives: очистить key потеряет выбор пользователя; сохранить общий namespace
не обеспечит разделения auth/guest. Кнопки reset в текущем ListingTable нет;
сброс означает удаление сохранённого ключа и повторное открытие.

## Авторы и отказ справочника

Decision: загружать users списком только при auth, ошибки возвращают [].
Rationale: Event/CupEvent уже делают это для карточек, но без catch ошибка
прерывала load. PersonViewPage нуждается в users для новых ячеек.
getUsers кэширует список; refreshUsers дедуплицирует одновременные запросы.
Alternatives: запрос по автору на каждую строку создаёт рост нагрузки.

## Архитектура и проверка

Decision: только чтение и отображение, без domain mutation и schema change.
Rationale: 050 уже добавила аудит; новые значения читаются из атрибутов без SQL.
Проверить контракт обоих endpoint с различающимися created/updated, список из
нескольких строк, гостевое отсутствие ключей, UI реальный ListingTable и
существующие ImpressionDetails tests. Установленные версии и паттерны изучены
по локальному коду, web research не нужен.
