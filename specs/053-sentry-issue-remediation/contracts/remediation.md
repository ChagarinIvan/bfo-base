# Remediation contracts

## HTTP startup

Некорректный multipart POST на неизвестный маршрут не выдаёт PHP diagnostics в body.
Ответ сохраняет исходный HTTP status приложения, обычно 404 для неизвестного пути.
Startup warning остаётся в error log. Неожиданные application exceptions продолжают
логироваться и отправляться в Sentry по существующей конфигурации.

## Protocol import

create(Event, inputs, actor) сохраняет порядок и количество строк, нормализацию,
связь Distance с Event и прежние поля/Impression. Все существующие активные группы
читаются одним groups SELECT на непустой вызов, пустой вызов не читает groups.
Отсутствующее имя создаётся один раз, inactive Group не используется. Ошибка lookup
остаётся обёрнутой в UnableToCreateProtocolLine. Кэш не переживает вызов create.

## Person assignment

ShouldQueueAfterCommit, commands и event payload сохраняются. Cleanup отсутствующего
прежнего Person после reassignment считается уже завершённым. Прочие исключения
продолжают прерывать job; прямой DisablePerson отсутствующей записи даёт PersonNotFound.
Повторный cleanup не создаёт новый PersonDisabled и не меняет audit time.

## Deployment

Новый image должен получить ini через существующий COPY/mount. До возобновления
Horizon обязательны миграции текущей версии; после deploy перезапускаются workers.
Статус resolved в Sentry не заменяет проверку схемы, версии процесса и повторения
исходных сценариев. Автоматическое закрытие issues этим изменением не выполняется.
