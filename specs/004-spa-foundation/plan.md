# Реализационный план: Фронтенд-фундамент — постепенный переход на SPA

**Ветка**: `004-spa-foundation` | **Дата**: 2026-08-29 | **Спек**: [spec.md](spec.md)

**Входные данные**: Спецификация фичи из `specs/004-spa-foundation/spec.md`

---

## Резюме

Фича закладывает технический фундамент для постепенного перехода BFO Base с
Blade-монолита на Vue 3 SPA + JSON API. Бэкенд: Sanctum API-токены (Bearer) для
аутентификации, новый версионированный API (`/api/v1/*`) с изоляцией в
`ApiV1RoutesServiceProvider` и `Api\V1\` контроллерах, PHPUnit тест-кейс
`ApiV1TestCase` для покрытия envelope-контракта. Фронтенд: Vite + TypeScript +
Vue 3 Composition API + PrimeVue 4, собирается в `public/spa/`, сосуществует с
существующим laravel-mix без конфликтов. Nginx получает один дополнительный
location-блок для SPA-роутинга. Пилот — две страницы соревнований: публичный список
(`/app/competitions`) и приватная форма создания (`/app/competitions/create`).

---

## Технический контекст

**Язык/Версия**: PHP 8.5 (бэкенд) + TypeScript 5.x strict (фронтенд)

**Основные зависимости**:
- Backend: Laravel 13, Laravel Sanctum (уже в проекте, `config/sanctum.php` есть),
  PHPUnit
- Frontend: Vue 3.x, Vite 6.x, PrimeVue 4.x (Aura theme), Vue Router 4,
  Pinia 2, Axios, ESLint, Prettier, Vitest

**Хранение**: MySQL 8.4 (Eloquent). Sanctum требует таблицу `personal_access_tokens`
— миграция публикуется через `php artisan vendor:publish --tag=sanctum-migrations`
; срок действия токенов задаётся `config/sanctum.php` как `expiration = 1440`.

**Тестирование**: PHPUnit (`composer test`) + Vitest (`npm run test`) для фронта

**Целевая платформа**: Nginx 1.28 + PHP-FPM 8.5 + MySQL 8.4 (Docker Compose)

**Тип проекта**: Web-приложение — Laravel API бэкенд + Vue 3 SPA фронтенд в
одном репозитории

**Ограничения**: Zero регрессий; Blade-сайт продолжает работать без изменений;
Vite и laravel-mix не пересекаются по путям вывода

**Масштаб**: Пилот — 5 API-эндпоинтов (auth: 3, competitions: 2), 3 SPA-страницы;
фундамент рассчитан на N последующих миграций страниц

---

## Constitution Check

*Гейт: должен пройти до Phase 0; повторная проверка после Phase 1.*

| Принцип | Статус | Примечание |
|---|---|---|
| I. Слоистая архитектура | ✅ | V1-контроллеры в `Bridge\Laravel\Http\Controllers\Api\V1\`; вызывают `ListCompetitionsService` / `AddCompetitionService` из Application-слоя напрямую |
| I. Никаких новых Services/Repositories | ✅ | Application-слой не меняется; никаких новых Services/Repositories |
| II. Без фасадов | ✅ | Токен берётся из `$request->user()`, не `Auth::user()`; зависимости через конструктор |
| II. Интерфейсы | ✅ | Контроллеры зависят от `ListCompetitionsService`, `AddCompetitionService` — инжектируются через конструктор |
| III. Тесты обязательны | ✅ | `ApiV1TestCase` + тесты для каждого эндпоинта; Vitest для фронта |
| IV. Целевая архитектура важнее скорости | ✅ | Новый код идёт в Bridge, не в легаси |
| V. Только вперёд | ✅ | Vue 3, Vite 6, PrimeVue 4, TypeScript strict, PHP 8.5 |
| VI. Импорт вместо FQCN | ✅ | Соблюдать: `use` + короткое имя во всех новых PHP-файлах |
| N+1 | ✅ | `byCriteria()` — один запрос с year-фильтром; нет eager-loading рисков в Competition |
| Auth policy | ✅ | Любой валидный Sanctum-токен даёт доступ к созданию; роли и permissions в этой фиче не вводятся |
| Token lifecycle | ✅ | Sanctum tokens имеют TTL 1440 минут; refresh-token flow отсутствует, после истечения выполняется login заново |
| Conditional representation | ✅ | Публичный GET остаётся доступным без токена; `created`/`updated` добавляются только при валидном Bearer |
| Date validation | ✅ | `to >= from`; однодневные соревнования разрешены |
| Sanctum boundary | ✅ | Sanctum adapter и `HasApiTokens` размещаются в `Infrastructure/Sanctum`; `Domain\User\User` не импортирует Sanctum |

---

## Структура проекта

### Документация (эта фича)

```text
specs/004-spa-foundation/
├── plan.md              ← этот файл
├── research.md          ← Phase 0
├── data-model.md        ← Phase 1
├── quickstart.md        ← Phase 1
├── contracts/           ← Phase 1
│   ├── api-envelope.md
│   ├── api-auth.md
│   ├── api-competitions.md
│   └── api-v1-manifest.md   ← нормативный манифест нового JSON API
└── tasks.md             ← Phase 2 (speckit-tasks)
```

### Исходный код (корень репозитория)

```text
# Бэкенд — новые файлы
app/
├── Domain/
│   └── User/
│       └── User.php                          ← без Sanctum-зависимости
├── Infrastructure/
│   └── Sanctum/
│       └── SanctumUser.php                   ← auth adapter + HasApiTokens
└── Bridge/
    └── Laravel/
        ├── Http/
        │   ├── Controllers/
        │   │   └── Api/
        │   │       └── V1/                   ← новый namespace (старый Api\ не трогаем)
        │   │           ├── Auth/
        │   │           │   ├── LoginAction.php
        │   │           │   └── LogoutAction.php
        │   │           ├── User/
        │   │           │   └── ListUsersAction.php
        │   │           └── Competition/
        │   │               ├── ListCompetitionsAction.php
        │   │               └── CreateCompetitionAction.php
        │   └── Serialization/
        │       └── ApiDtoSerializer.php     ← группы public/authenticated
        └── Provider/
            └── ApiV1RoutesServiceProvider.php ← новый (ApiRoutesServiceProvider не трогаем)

# Бэкенд — тесты
tests/
└── Feature/
    └── Api/
        └── V1/                               ← новый namespace
            ├── ApiV1TestCase.php             ← абстрактный базовый класс
            ├── Auth/
            │   └── AuthTest.php
            └── Competition/
                ├── ListCompetitionsTest.php
                └── CreateCompetitionTest.php

# Бэкенд — инфраструктура/конфиг
database/
└── migrations/
    └── XXXX_XX_XX_create_personal_access_tokens_table.php ← публикуется из Sanctum

# Фронтенд — новые файлы (отдельно от resources/js и resources/css)
resources/
└── spa/
    ├── index.html                            ← Vite entry HTML
    ├── main.ts                               ← точка входа Vue app
    ├── App.vue
    ├── router/
    │   └── index.ts                          ← Vue Router: /app/* маршруты
    ├── stores/
    │   └── auth.ts                           ← Pinia auth store (token + user)
    ├── api/
    │   └── client.ts                         ← Axios с Bearer interceptor + 401 handler
    ├── pages/
    │   ├── auth/
    │   │   └── LoginPage.vue
    │   └── competitions/
    │       ├── CompetitionsPage.vue           ← публичная, список + year filter
    │       └── CreateCompetitionPage.vue      ← приватная, форма создания
    └── components/
        └── AppLayout.vue                      ← обёртка с header/nav

# Фронтенд — артефакты сборки
public/
└── spa/                                      ← Vite build output (gitignore-кандидат, или commit)
    ├── index.html
    └── assets/
        ├── main-[hash].js
        └── main-[hash].css

# Конфигурация
vite.config.ts                                ← корень репо
tsconfig.json                                 ← strict mode
eslint.config.js                              ← ESLint 9 flat config + Vue 3 + TypeScript
.prettierrc.js                                ← Prettier
config/sanctum.php                             ← expiration = 1440 минут
FRONTEND.md                                   ← руководство разработчика

# Nginx (правка существующего файла)
enviroment/nginx/conf.d/                      ← добавить location /app { ... }
```

**Решение по структуре**: фронтенд исходники в `resources/spa/` (изолировано от
`resources/js/` laravel-mix), сборка в `public/spa/`. Vite конфигурация — в корне
репо. `package.json` существует — расширяем зависимости и скрипты.

**REST conventions**: V1 API не ограничивается одним HTTP-методом; метод выбирается
по семантике операции. В текущем scope используются `GET` для чтения, `POST` для
создания и `DELETE` для отзыва текущего auth-токена.

---

## Complexity Tracking

| Нарушение | Почему необходимо | Почему более простая альтернатива отвергнута |
|---|---|---|
| Sanctum auth adapter в Infrastructure | Laravel Sanctum требует `HasApiTokens` на authenticatable-модели | Адаптер сохраняет Sanctum за границей Domain; auth provider направляется на `Infrastructure\\Sanctum\\SanctumUser` |
| Vite + laravel-mix сосуществование | Старый фронт работает на laravel-mix; нельзя мигрировать всё сразу | Полный переход на Vite сейчас сломал бы Blade-фронт; разные пути вывода (`public/js` vs `public/spa`) решают конфликт |
| Пагинация на уровне Bridge | `ListCompetitionsService` возвращает `ViewCompetitionDto[]` без пагинации | Pagination envelope формируется отдельным API adapter-слоем, не Application service и не контроллером |
| Условные поля DTO на уровне Bridge | Публичный endpoint меняет представление в зависимости от валидного Bearer | `ApiDtoSerializer` применяет группы `public`/`authenticated`; DTO и Domain не зависят от HTTP |
