# Research

## Decision: Codex Action

Официальный action поддерживает prompt-file, output-schema-file, working-directory,
model/effort, read-only sandbox и drop-sudo. Проверен официальный action.yml v1.
Источник: https://learn.chatgpt.com/docs/github-action и
https://github.com/openai/codex-action/blob/v1/action.yml.
Вместо собственного API клиента используем action с proxy для защиты ключа.

## Decision: доверенная orchestration

pull_request_target исполняет trusted workflow; base SHA checkout содержит controls.
Head SHA checkout не используется как working directory агента. Никакие npm/composer
scripts из PR не запускаются. Same-repo gate и встроенный write-access gate Action.
Publisher отдельно проверяет live base/head, schema и пути. Агентный текст не shell.
Источник: https://docs.github.com/en/actions/reference/security/secure-use.
Read-only research agent проверил границы прав, config loading, artifacts и freshness.

## Decision: deterministic aggregation

Одинаковые path/start/end/title объединяются с сохранением roles и самого высокого
priority. Семантически похожие, но разные findings сохраняются: опасно поручать
модели молча удалять различающиеся дефекты. JSON status completed/not_applicable/partial.
Отсутствующий/невалидный artifact превращается в error, не clean review.

## Decision: source UI review

Сравнение с существующими PrimeVue компонентами, tokens/themes и by.json.
Без screenshots нельзя заявлять визуальную верификацию. Browser suite вне scope.

## Decision: defaults and limits

Модель не фиксируем без предпочтения пользователя; repository variable переопределяет
Action default. Два одновременных задания, 15 минут каждое. API project budget
настраивается владельцем. Нет новых runtime зависимостей, нет NEEDS CLARIFICATION.
