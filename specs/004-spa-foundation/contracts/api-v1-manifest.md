# Манифест нового JSON API

**Статус**: нормативный контракт для нового API  
**Версия**: v1  
**Namespace**: `/api/v1/*`  
**Дата**: 2026-08-29

Этот манифест относится только к новому версионированному JSON API. Legacy API,
Blade-маршруты, старые `Api\`-контроллеры и `ApiRoutesServiceProvider` находятся
вне области манифеста и не должны изменяться в рамках этой фичи.

## Архитектура

- HTTP boundary находится в `App\Bridge\Laravel\Http\Controllers\Api\V1`.
- Action-классы принимают Application DTO, передают command в Application service
  и возвращают `View*Dto` или массив DTO.
- Action-классы не работают с полями `Request`, Eloquent и ручной сериализацией.
- Application service принимает command, содержащую входной DTO, и возвращает
  `View*Dto`.
- Application service зависит от доменных интерфейсов; Laravel/Sanctum адаптеры
  находятся в Infrastructure.
- DTO-маппинг выполняют отдельные Assembler-классы рядом с DTO. Например,
  `LoginAssembler` преобразует доменный `AccessToken` в `ViewTokenDto`.
- Пагинация выполняется на уровне доменного репозитория через `Slice` и
  Pagerfanta adapter над query builder. Application получает только текущую
  страницу; загрузка всех записей в память запрещена.
- `LogoutAction` получает `UserId` из authenticated context и передаёт его в
  `Logout` command и `LogoutService`; отзыв текущего Sanctum-токена выполняет
  `SanctumCurrentTokenRevoker` через доменный `CurrentTokenRevoker`.

## Представление и ошибки

- Все DTO сериализуются общим Bridge-сериализатором.
- Поля с `#[Groups(['authenticated'])]` выдаются только при валидном Bearer-токене.
- Application service преобразует ожидаемые доменные ошибки в специальные
  Application-ошибки.
- Общий API action adapter автоматически читает `#[HttpError]` и формирует
  `{ "errors": [{ "code": "...", "message": "..." }] }`.
- `Handler` не используется для преобразования ошибок нового API.
- Пароли и внутренние поля пользователей никогда не попадают в DTO ответа.

## Именование параметров V1

- Все имена параметров в query string и JSON request body нового V1 API используют `camelCase`.
  Это относится и к техническим параметрам: `perPage`, а не `per_page`, и `competitionId`, а не
  `competition_id`.
- Имена PHP-свойств, Eloquent-колонок и legacy API не являются частью этого правила. Их не
  переименовывают ради V1-совместимости.
- Каждый новый или изменённый V1 endpoint покрывается request-тестом с его camelCase-параметрами.

## Аутентификация

- Используется Sanctum Bearer token.
- Токен хранится на frontend в Pinia и `localStorage` с учётом XSS-рисков.
- TTL токена — 1440 минут.
- Refresh token отсутствует; после истечения токена выполняется повторный login.

## Дополнение к архитектуре

Исключение для read-only списка годов: Application use case ListYears не
принимает command, потому что у endpoint нет входных параметров.

## Область v1

- `POST /api/v1/auth/login`
- `DELETE /api/v1/auth/logout`
- `GET /api/v1/users` — приватный полный список пользователей без входного DTO,
  фильтров и пагинации;
  используется для отображения авторов impression-полей
- `GET /api/v1/competitions`
- `POST /api/v1/competitions`
- `GET /api/v1/years`

Текущий authenticated user не подставляется Laravel в action только по type-hint.
`ApiAction` получает его через `$request->user()` и регистрирует в контейнере как
`App\Domain\Auth\User` и `UserId`; поэтому actions, которым нужен пользователь,
могут принимать их типизированными параметрами `__invoke`.

Пилот SPA использует публичный список соревнований и защищённое создание
соревнования. Полная миграция legacy-разделов и mobile scope отсутствуют.

Пользовательский интерфейс SPA отображается на белорусском языке. Тексты не
зашиваются в Vue-шаблоны: они берутся по ключам из существующего словаря
`resources/lang/by.json` через типизированный адаптер `resources/spa/i18n.ts`.

## Визуальная база SPA

По умолчанию SPA наследует визуальные решения legacy Blade-интерфейса:
тёмный заметный navbar, Bootstrap-подобные контейнеры, зелёные action-кнопки,
табличную компоновку и знакомые подписи. Для интерактивных элементов SPA
используются PrimeVue-компоненты, а не нативные поля HTML. Отклонения от
визуальной базы Blade допускаются только по явному требованию фичи.
