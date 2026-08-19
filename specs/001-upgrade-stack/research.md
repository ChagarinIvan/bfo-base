# Research: Обновление технологического стека

**Date**: 2026-08-19 | **Feature**: 001-upgrade-stack

Сведение результатов двух исследований: (A) скан кодовой базы на предмет реального использования
обновляемых библиотек (blast radius), (B) факты о версиях/совместимости из официальных источников
(laravel.com, php.net) и метаданных composer.

> ⚠️ **Freshness caveat**: часть точных latest-патчей и per-major min-PHP не удалось подтвердить живьём
> (packagist/GitHub были недоступны части ресёрча). Помечено `[verify-live]` — обязательно перепроверить
> `composer` / packagist на этапе tasks/implement перед фиксацией версий.

---

## Blast radius в коде (находки скана)

| Библиотека     | Прямое использование                                                                                              | Файлы                                                                                     | Тесты                  |
|----------------|-------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------|------------------------|
| phpspreadsheet | Только Reader-API (`Reader\Xlsx/Xls`, `load()`, `getActiveSheet()`, `toArray()`). Писателей/стилей нет            | `app/Models/Parser/{XlsxParser,XlsParser,OldObelarusNetXlsxParser,ElkPathXlsxParser}.php` | 18 parser-тестов       |
| predis         | Нет прямых вызовов; дефолт `REDIS_CLIENT=phpredis` (`config/database.php:120`)                                    | —                                                                                         | —                      |
| doctrine/dbal  | Нет; один мёртвый `use Doctrine\DBAL\Types\Type;` в миграции `2024_03_12_100012_...`                              | migrations                                                                                | —                      |
| guzzle         | Нет прямых вызовов; только транспорт под Laravel HTTP                                                             | —                                                                                         | —                      |
| Laravel        | Чистый DDD, 1 кастомный фасад (`Bridge/Laravel/Facades/Color.php`), 16 стандартных провайдеров, 15 artisan-команд | app/Bridge, app/Providers                                                                 | ~115 тест-файлов всего |

Тестовая база: **~115 тест-файлов** (unit по Domain/Application/Services + feature/request по
Bridge/Laravel/Http/Controllers). `phpunit.xml`: `QUEUE_CONNECTION=sync`, `CACHE_DRIVER=array`,
`SESSION_DRIVER=array`.

---

## Decision 1 — doctrine/dbal: УДАЛИТЬ, а не апгрейдить

- **Decision**: Убрать `doctrine/dbal` из `composer.json` и удалить мёртвый `use` в миграции.
- **Rationale**: Laravel 11+ не требует dbal для `->change()` (нативные schema-операции). Прямого
  использования нет. Удаление убирает целый мажорный апгрейд (3→4) из scope.
- **Alternatives**: Апгрейд 3→4 — отвергнут: лишняя работа ради неиспользуемой зависимости.

## Decision 2 — Redis-клиент: стандартизировать на phpredis, predis выкинуть

- **Decision**: Убедиться, что расширение **phpredis** установлено во всех окружениях (включая Horizon-воркеры),
  и удалить `predis/predis`. Если по какой-то причине нужен чистый PHP-клиент — прыгнуть сразу на predis 3.x, не оставаться на 1.1.
- **Rationale**: Дефолт `REDIS_CLIENT=phpredis`; predis 1.1.10 (2022) — фактически спящая зависимость.
  Laravel рекомендует phpredis для производительности.
- **Риск/проверка**: подтвердить наличие phpredis в контейнере PHP и на воркерах до удаления predis;
  сверить опции сериализации/сжатия (`REDIS_*`). На PHP 8.5 проверить наличие сборки phpredis `[verify-live]`.
- **Alternatives**: Оставить predis 1.1 — отвергнуто (устарело, конфликт с «только вперёд»).

## Decision 3 — phpspreadsheet 1.x → 5.x идёт РАНО (гейт для PHP 8.5)

- **Decision**: Поднять phpspreadsheet до 5.x **до** апгрейда PHP до 8.5.
- **Rationale**: phpspreadsheet 1.30 имеет жёсткий предел `php <8.5` — блокирует PHP 8.5. При этом
  в проекте используется только Reader-API, а удалённые в 2.0 методы (`*ByColumnAndRow`) **не используются** →
  код-импакт близок к нулю.
- **BC на пути**: 2.0 удалил `*ByColumnAndRow` (у нас не встречаются); далее ужесточение типов/enum в 3/4/5.
  Точные per-major min-PHP `[verify-live]`; 5.x — современный PHP.
- **Alternatives**: Держать phpspreadsheet последним (как в исходном порядке спеки) — отвергнуто: он
  блокирует PHP 8.5, а риск низкий, поэтому логично раньше.

## Decision 4 — guzzle: снять точечный пин, мажор 8 условен

- **Decision**: Изменить `guzzlehttp/guzzle` с `7.10.0` на `^7.10`. Мажор 8 — только если его допускает
  ограничение фреймворка (Laravel зависит от `guzzlehttp/guzzle ^7`).
- **Rationale**: Точечный пин блокирует патчи и конфликтует с constraint фреймворка. Прямого guzzle-кода нет —
  функциональной миграции не требуется. Стабильность guzzle 8 и его допустимость Laravel 13 — `[verify-live]`.
- **Alternatives**: Форсить guzzle 8 сейчас — отвергнуто до подтверждения совместимости с Laravel.

## Decision 5 — Laravel по одному мажору (11→12→13) через Rector

- **Decision**: 11→12, затем 12→13, отдельными шагами. Rector: сначала `LaravelLevelSetList::UP_TO_LARAVEL_120`
  (в `rector.php` сейчас `UP_TO_LARAVEL_110` — поднять), затем `LaravelSetList::LARAVEL_130`.
- **Факты**: L12 — min PHP 8.2, лёгкий апгрейд; ключевое app-facing: **UUIDv7** у `HasUuids` (для v4 —
  `HasVersion4Uuids`), **Carbon 2→3**, `image`-валидация больше не пускает SVG, `local` disk root →
  `storage/app/private`. L13 — стабилен (релиз 17.03.2026), min PHP 8.3, поддерживает 8.5; ключевое:
  **CSRF middleware `VerifyCsrfToken` → `PreventRequestForgery`** (старое имя алиасится), cache
  `serializable_classes` по умолчанию `false`, сериализация сессий по умолчанию `json` (инвалидирует
  активные сессии), `upsert` с пустым `uniqueBy` бросает исключение, переименования свойств событий.
- **Проверить в коде**: использование `HasUuids`, Carbon-специфики, SVG-загрузок, кастомных ссылок на
  `VerifyCsrfToken`, кэширование объектов, а также клеш глобальных `array_first()/array_last()`
  (полифилл PHP 8.5 в L13) с легаси-хелперами.
- **Horizon/sentry**: сверить, что `laravel/horizon` и `sentry/sentry-laravel` имеют версии под L12/L13 `[verify-live]`.

## Decision 6 — PHP 8.4 → 8.5 после phpspreadsheet и проверки расширений

- **Decision**: PHP 8.5 — после апгрейда phpspreadsheet (гейт) и подтверждения сборок C-расширений
  (phpredis, gd/exif, mbstring, zip и т.п.) под 8.5.
- **Факты**: PHP 8.5 GA 20.11.2025, latest 8.5.8. BC: изменения значений PDO fetch-mode констант и
  их валидация, депрекейт `PDO::MYSQL_ATTR_*`, предупреждения на lossy float→int и деструктуризацию не-массива,
  депрекейт неканоничных кастов `(integer)/(boolean)`, backtick-оператора, `curl_close()`.
- **Alternatives**: PHP 8.5 первым шагом — отвергнуто (блокируется phpspreadsheet 1.x и расширениями).

## Decision 7 — rector/rector-laravel → driftingly/rector-laravel

- **Decision**: `composer require --dev driftingly/rector-laravel` (замена заброшенного `rector/rector-laravel`).
  Требует `rector/rector ^2.2.7`.
- **Факты**: заброшенность подтверждена флагом в `composer.lock`; driftingly — актуальный поддерживаемый форк.
  В 2.2.0 есть `UP_TO_LARAVEL_120` (кумулятивный) и `LaravelSetList::LARAVEL_130` (не кумулятивный —
  подключать явно). Возможен более новый релиз с `UP_TO_LARAVEL_130` `[verify-live]`.

---

## Итоговый порядок шагов (уточнённый относительно спеки)

Спека допускала уточнение порядка по совместимости — вот оно:

1. **Безопасные semver-обновления** dev-инструментов + horizon/sentry-минор; замена rector-laravel на driftingly.
2. **Сжать поверхность**: удалить doctrine/dbal (+мёртвый импорт); снять пин guzzle → `^7.10`;
   стандартизировать Redis на phpredis и удалить predis (после проверки расширения).
3. **phpspreadsheet 1.x → 5.x** (гейт для PHP 8.5; код-импакт минимален).
4. **Laravel 11 → 12** (rector `UP_TO_LARAVEL_120`; UUIDv7 / Carbon 3 / SVG).
5. **Laravel 12 → 13** (rector `LARAVEL_130`; CSRF-rename, дефолты cache/session).
6. **PHP 8.4 → 8.5** (после шага 3 и проверки сборок расширений).
7. **Инфра-образы**: MySQL 8.0.27 → 8.4 LTS, Redis 6.2 → 7/8, Nginx → текущий стабильный (с бэкапом БД).

## Список для `[verify-live]` перед фиксацией версий

- Точные latest-патчи: Laravel 13.x, phpspreadsheet 5.x, predis 3.x, phpunit 13.x, horizon, sentry-laravel.
- Существует ли стабильный guzzle 8 и допускает ли его Laravel 13 (constraint `^7` vs `^8`).
- Наличие сборок phpredis (и др. C-расширений) под PHP 8.5.
- Совместимость `laravel/horizon` и `sentry/sentry-laravel` с Laravel 12 и 13.
- Наличие `UP_TO_LARAVEL_130` в актуальной версии driftingly/rector-laravel.
