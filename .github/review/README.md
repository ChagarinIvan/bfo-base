# Agent PR review

Шесть независимых ролей: architecture, ddd, security, tests, frontend, uiux.
Правила находятся в common.md и roles/*.md; JSON-контракт в report.schema.json.
Замечания рекомендательные. Неполное ревью отмечается в PR и ошибкой publisher job.
Не делайте Agent PR review обязательным merge check до оценки качества на реальных PR.

## Подключение

1. Смержить workflow, scripts и prompts в master после обычного code review.
   Используется pull_request_target: до trusted установки на base workflow не активен.
2. В GitHub repository Settings → Secrets and variables → Actions создать secret
   `OPENAI_API_KEY` с отдельным OpenAI project key. Не добавлять ключ в код или PR.
3. Опционально создать repository variable `CODEX_REVIEW_MODEL` с доступным вашему
   API project идентификатором модели. Пустая переменная использует default Codex Action.
   Доступность конкретной модели проверяется первым реальным запуском.
4. Убедиться, что настройки организации/репозитория разрешают workflow token создавать
   issue comments. Publisher запрашивает `issues: write`, reviewers только `contents: read`.
   GitHub PR approvals от бота не используются, настройка разрешения создавать reviews не нужна.
5. Открыть или обновить nondraft PR из ветки этого же репозитория в master.
   Triggers: opened, synchronize, reopened, ready_for_review. Fork PR пропускаются.
   Codex Action дополнительно проверяет write access инициатора; bypass для всех пользователей
   и ботов не включён. Изменение только base ветки само по себе не запускает ревью.
6. Проверить шесть jobs и один комментарий с точным head/base SHA. Выполнить небольшой
   второй push, убедиться, что комментарий обновляется. Повторить через Re-run all jobs.
   Не использовать Re-run failed jobs для полного отчёта: успешные роли не создадут
   новые artifacts, и сборка может оказаться неполной.

## Права и доверие

Workflow/скрипты/prompts/schema берутся с immutable base SHA. Candidate head SHA
выгружается отдельно только для чтения. Working directory Codex находится в trusted
checkout: PR-local AGENTS.md, .codex и scripts не являются активной конфигурацией.
Не добавляйте запуск composer/npm/git hooks/другого кода из candidate.
Агенты имеют read-only sandbox и отдельный UID codex-review без sudo; API key передаётся только Codex Action proxy.
Подготовка аккаунта даёт чтение двух checkout и запись только в отдельный каталог результатов.
Coverage metadata остаются под владельцем runner и защищены sticky bit каталога.
Публикация происходит на отдельном runner без API key, candidate кода и его scripts.
Model output проверяется по schema, role/SHA, changed paths и ограничениям, экранируется
как текст. Новые или закрытые PR не получают старый отчёт. Actions закреплены SHA;
обновляйте их осознанно вместе с проверкой контракта. Codex CLI используется default
версии Action; если нужна воспроизводимость модели/CLI, зафиксируйте model и codex-version.

## Время и расходы

До двух ролей параллельно, 15 минут на каждую, publisher 5 минут; устаревшие запуски
отменяются. Шесть ролей означают до шести платных model sessions на обновление PR.
Установите бюджет и уведомления в OpenAI project. Timeout не является жёстким лимитом
стоимости или токенов. Secrets/API billing независимы от локального входа в приложение Codex.

До 12 findings на роль. Более 150 changed files или 1 MiB diff требуют partial.
Diff не обрезается молча. Report artifact до 128 KiB; общий комментарий до 60k символов.
Artifacts хранятся 7 дней. Только role JSON и доверенные coverage metadata, без полного transcript или proxy logs.

## Результат и ограничения

completed: применимая область просмотрена; not_applicable: релевантных изменений нет;
partial: неполное покрытие с объяснением; error: нет валидного результата.
Отсутствующий ключ, таймаут или плохой JSON никогда не становятся «замечаний нет».
Дубли объединяются по файлу, диапазону и нормализованному заголовку с сохранением
ролей и наивысшего priority. Похожие замечания с разным смыслом сохраняются.
Все шесть ролей запускаются: каждая сама указывает неприменимость при отсутствии области.

UI/UX сравнивает исходники с общими компонентами, темами и белорусскими текстами.
Скриншоты, фактический render, измерение contrast и browser tests не выполняются.
Агенты не исправляют код и не мержат PR; обычные PHPUnit/PHPStan/CS/Rector/frontend CI остаются.

## Локальная проверка

```sh
node --test .github/review/review.test.cjs
actionlint .github/workflows/agent-review.yml .github/workflows/laravel.yml
```

Node 22 в CI, внешних npm-зависимостей для reviewer tooling нет. Tests не вызывают API.
Первый живой smoke после установки ключа отмечается в specs/055-agent-pr-review/validation.md.
Изменения правил начинают действовать после merge в trusted base, а не из текущего PR.

Официальные контракты: [Codex Action](https://learn.chatgpt.com/docs/github-action),
[GitHub Actions security](https://docs.github.com/en/actions/reference/security/secure-use).

## Troubleshooting: system_bus_socket

В run 42479209767 все роли упали до запуска Codex: drop-sudo не смог ограничить
доступ runner к /run/dbus/system_bus_socket. Это ошибка подготовки изоляции, не
подтверждение ошибки API-ключа или модели. Workflow использует поддерживаемый
upstream режим unprivileged-user с отдельным аккаунтом вместо изменения системных
сокетов. Sandbox read-only и отдельная публикация сохранены.

Из-за pull_request_target исправление сначала должно попасть в trusted master.
После этого нужен новый synchronize/reopened event PR, например новый push.
Re-run старого run использует прежнюю ревизию workflow и не применит исправление.
