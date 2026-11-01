# Runtime and dependencies contract

| Окружение | Версия | session.upload_progress.enabled |
| --- | --- | --- |
| Server Docker | php:8.5.12-fpm | 0 из startup ini |
| CI | 8.5.12 | 0 через setup-php ini-values |
| Local host | установленный 8.5.10 | не изменяется этой доработкой |

Composer root constraints сохраняются. Update выполняется разработчиком, сервер
использует install --no-dev из committed lock. CI проверяет production audit,
полный audit с dev graph записан отдельно. Никаких security suppressions и
platform overrides не добавляется. API, DTO, фильтры, пагинация и доменные правила
не изменяются. Production runtime и workers проверяются после выкладки пользователем.
