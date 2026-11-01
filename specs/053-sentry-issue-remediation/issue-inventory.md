# Sentry inventory

Снимок 2026-11-01 через авторизованный Sentry MCP, повторно подтверждён
2026-11-01 17:01:00 UTC тем же запросом, новых issues нет. Проект 4509139044335616,
query пустой, period=90d, sort=date, limit=100. Получено 9 записей, продолжения
выборки инструмент не сообщил. Это все issues доступного 90-дневного снимка,
а не обещание all-time полноты. 3 unresolved, 6 resolved. Все events прочитаны.
Environment production, runtime показанных событий PHP 8.5.10. Release для
достоверной привязки к git commit не предоставлен. Даты ниже UTC из issue details.
Частоты отражают details; число событий в окне search может отличаться, особенно F0.

| Issue | Статус снимка | First / last UTC | Events | Причина и решение |
| --- | --- | --- | --- | --- |
| [FC](https://chagarin.sentry.io/issues/BFO-BASE-FC) | unresolved | 2026-10-31 23:01:55 / то же | 1 | Fatal при повторном sendHeaders после FD; production startup ini и HTTP regression |
| [FD](https://chagarin.sentry.io/issues/BFO-BASE-FD) | unresolved | 2026-10-31 23:01:54 / то же | 1 | Warning headers already sent; startup output воспроизведён, точный исходный payload неизвестен |
| [F6](https://chagarin.sentry.io/issues/BFO-BASE-F6) | unresolved, regressed | 2026-10-11 15:40:28 / 2026-10-30 15:16:07 | 7 | SELECT группы на каждое distinct имя; batch lookup и кэши в пределах импорта |
| [FB](https://chagarin.sentry.io/issues/BFO-BASE-FB) | resolved | 2026-10-18 08:56:58 / 08:57:00 | 3 | PersonNotFound в повторном cleanup; точечная идемпотентность handler |
| [FA](https://chagarin.sentry.io/issues/BFO-BASE-FA) | resolved | 2026-10-18 08:56:07 / 08:56:08 | 3 | EventResultsCleanupError оборачивает SQL unknown next_rank_activation_on; историческая схема/выкладка |
| [F9](https://chagarin.sentry.io/issues/BFO-BASE-F9) | resolved | 2026-10-18 08:55:55 / 08:55:56 | 3 | Unknown next_rank_activation_on при save Person; миграция 047 и порядок deploy |
| [F7](https://chagarin.sentry.io/issues/BFO-BASE-F7) | resolved | 2026-10-17 21:22:48 / 2026-10-18 08:00:41 | 6 | RankFact bool получил null из collector; вероятна старая схема, проверить migration и defaults |
| [F8](https://chagarin.sentry.io/issues/BFO-BASE-F8) | resolved | 2026-10-18 00:10:03 / то же | 1 | Unknown next_rank_activation_on в expiry WHERE; миграция 047 и порядок deploy |
| [F0](https://chagarin.sentry.io/issues/BFO-BASE-F0) | resolved | 2026-09-17 17:33:14 / 2026-10-04 20:19:09 | 6 в details, 3 в search | ProtocolParser not instantiable в старом worker; текущий binding и container regression |

## FC and FD

Events `5db12b3efa6d4ad78fd80bee45473ef0` и `5cacd602b7244b8db1f93c3ebcd34a2a`,
общий trace `932ec628a9ad49e8be6537d92d2b6591`, POST неизвестного probe path.
FD: public/index.php:55 -> Response.send -> sendHeaders:328. FC: Laravel
HandleExceptions -> response500 -> тот же header(). FC breadcrumb содержит FD.
Trace не содержит spans; raw body и место исходного вывода недоступны.
Regression: ProductionPhpResponseTest, status/body/log настоящего PHP subprocess.
Результат на production требует пересборки image, перезапуска FPM и наблюдения.

## F6

Event `d95de189178843eab2b6201546263f3e`, transaction ParseEventProtocolHandler.
Query `select * from groups where active = ? and normalize_name = ? limit 1`.
Trace `c6e3911f844945a18c02ed414457e1c4` содержит 489 spans, 471 SQL. Работа
ограничена конкретным groups pattern; все остальные SQL не объявляются устранёнными.
Regression: StandardProtocolLinesFactoryTest, 1/50 distinct existing groups,
unknown repeated name, inactive group, empty input, sequential imports.

## FB

Event `6c7fc492c05842969c559cc974449fbf`. DisablePersonService:26,
ProtocolLinePersonAssignedHandler:37. Active-only lock уже выключенного Person
даёт null. Resolved статус не устраняет дефект текущего handler.
Regression: ProtocolLinePersonAssignedHandlerTest и существующий прямой not-found
тест DisablePersonServiceTest. Повторный cleanup безопасен, прочие сбои видимы.

## FA, F9, F8

Events `79bdd3fabe70491aaf3a8d9d7c9cbf97`, `c8a2445cd6414327b8a4904006f3ac08`,
`858ce0a57e074eba925cdd2733baec01`. Общая отсутствующая колонка из миграции
2026_10_17_000001_add_delayed_rank_activation. FA использует старый cleaner,
которого в текущем коде уже нет. F9 идёт через ranks rebuild при назначении,
F8 через scheduled expiry rebuild. Текущий код не должен скрывать SQL failures.
Проверки: DelayedRankActivationTest, UniteEventsTest, migration status локальной БД.
Production schema и порядок старого deploy не доступны через MCP, необходима
проверка при выкладке по quickstart. Нельзя утверждать, что migration уже есть в production.

## F7

Event `d16ff35f37474177a55b148964c37460`. EloquentRankFactsCollector:46 ->
RankFact constructor delayedActivation bool, фактически null. Миграция 047
добавляет NOT NULL DEFAULT false, collector читает полный ProtocolLine с bool cast.
Отсутствующая колонка объясняет null, но это вывод по коду и времени, а не доказанный
факт исходной БД. Проверить реальные defaults/collector; не добавлять ?? false.

## F0

Event `8e8ba450ddaa4b47a7850f7f1ee406ec`. BindingResolutionException при создании
ParseEventProtocolHandler/Service в Horizon. EventProvider содержит binding
ProtocolParser -> StandardProtocolParser и зарегистрирован в config/app.php.
Проверка: ParseEventProtocolHandlerTest. Старый загруженный worker является
вероятной причиной при правильном текущем binding; старый deployment не доступен.

## Individual local outcomes

| Issue | Результат локальной работы | Что остаётся на production |
| --- | --- | --- |
| FC | Вложенный fatal устранён в startup HTTP-регрессии production ini | Deploy ini/image, проверить FPM overrides и новые события |
| FD | Исходный warning больше не повреждает response, warning сохраняется в log | Проверить новый runtime и повторения; точный исходный output неизвестен |
| F6 | Batch lookup, 1 SELECT для 1/50 групп; empty=0, cache isolation проверена | Повторить импорт и проверить новый performance event |
| FB | Повторная доставка проходит, один PersonDisabled, storage failure видим | Перезапустить workers, проверить повторные jobs |
| FA | Текущий cleanup и schema/ranks контракты проходят; старый cleaner удалён | Подтвердить миграцию 047 до workers |
| F9 | Сохранение next_rank_activation_on проверено ranks integration | Подтвердить миграцию 047 |
| F7 | Bool false/true и реальный collector проверены ranks integration, fallback не добавлен | Подтвердить колонку/default; точный старый источник null не установлен |
| F8 | Expiry WHERE и scheduled rebuild проверены integration | Подтвердить миграцию и новый scheduler/worker |
| F0 | Контейнер собирает handler и parser с текущим binding | Убедиться, что Horizon загрузил новую версию |

Все 9 разобраны, 4 issue IDs связаны с тремя локальными исправлениями,
5 имеют проверки текущего кода и конкретные действия выкладки. Sentry status
не изменялся; production success до deploy и наблюдения не подтверждён.
