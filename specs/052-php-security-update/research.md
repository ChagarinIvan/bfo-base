# Research: PHP security update

## Runtime

Decision: PHP 8.5.12 для server/CI.
Rationale: официальный стабильный security release с исправлениями
CVE-2026-109618 и CVE-2026-109619; official-images содержит 8.5.12-fpm.
Alternatives: PHP 8.6 RC не production, плавающий 8.5 не фиксирует исправленный patch.

Источники, проверены 2026-11-01:
- https://www.php.net/archive/2026.php#2026-10-21-5
- https://www.php.net/ChangeLog-8.php#8.5.12
- https://raw.githubusercontent.com/docker-library/official-images/master/library/php

## Дополнительная рекомендация PHP

Decision: tracked 99-security.ini с session.upload_progress.enabled=0 в образе и CI.
Rationale: сообщение PHP 23 октября по CVE-2026-115560. Нет использования функции
или переключения serializer в app/config. Обычные uploads не выключаются.
Alternatives: ini_set применяется слишком поздно для request parsing.
Source: https://www.php.net/archive/2026.php#2026-10-23-1

## Local PHP и область работы

Decision: не обновлять локальный PHP и не запускать Docker. Composer ^8.5 остаётся.
Rationale: уточнение пользователя; Composer update на реальном 8.5.10 проходит.
Alternatives: config.platform.php не подтверждает actual runtime и не используется.
Первоначальный Homebrew upgrade остановлен; PHP остался 8.5.10. Дополнительные
репозиторные файлы возвращены в PR после уточнения о spec-kit и Composer update.

## Dependencies и audit

Decision: полный update в текущих constraints, production audit в CI, полный audit
с отдельным указанием abandoned dev dependency.
Rationale: driftingly/rector-laravel 2.7.3 требует
symplify/rule-doc-generator-contracts ^11.2. У него нет указанной замены.
Известных advisories нет, warning не подавляется и не объявляется vulnerability.
Alternatives: ручное удаление dependency из lock ломает воспроизводимость;
смена major или удаление Rector выходит за рамки обновления совместимых пакетов.
