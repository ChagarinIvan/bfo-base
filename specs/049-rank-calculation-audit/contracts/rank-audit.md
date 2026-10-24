# Контракт простого журнала разрядов

GET /api/v1/persons/{personId}/rank-audit. Авторизация обязательна; page/perPage и стандартные X-Pagination-* headers. Сортировка id DESC; неизвестный/неактивный спортсмен: 404; аноним: 401.

Одна запись содержит только id, personId, trigger, created, before, after. created использует стандартный ImpressionDto: at и by. Trigger принимает manual, queued, scheduled, refill, protocol_import, protocol_cleanup, activation, reassignment или correction. Снимки содержат current, history, nextActivationOn, даты спортивных фактов YYYY-MM-DD. При ошибке after=null. Отдельного summary endpoint нет.

Фронт показывает trigger, created и различия двух состояний. Для анализа удалённого протокола остаются сохранённые значения/IDs в истории. Предусмотрены загрузка, пустой журнал с пояснением о начале аудита, ошибка/retry, постраничный просмотр и защита от устаревшего ответа при смене спортсмена. Пересчёт без изменений не добавляет запись.

CLI: persons:ranks:correct {userId} {--personId=} {--apply}. По умолчанию preview без записи; вывод IDs и состояний до/после без ФИО. Apply сохраняет отличающуюся проекцию по общей политике, журналирует trigger correction, не меняет ручные даты и повторно не создаёт изменений.
