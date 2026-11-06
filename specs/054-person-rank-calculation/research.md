# Research

## Причины и источники

Decision: RankCalculationReason, string backed enum с отдельными причинами бизнес-сценариев.

| Reason | Существующий источник |
| --- | --- |
| unknown | Историческая причина неизвестна или новый Person без расчёта |
| manual | RebuildPersonRanksAction; ProtocolLineRankActivatedHandler после ручной активации/изменения даты |
| event_results_processed | StandardEventPersonRankUpdater |
| event_results_cleared | CleanupEventResultsService |
| protocol_person_changed | SetPersonToProtocolLinesService для нового и прежнего Person |
| protocol | Совместимость существующих записей и старых заданий |
| daily | RebuildExpiredPersonRanksCommand, ежедневная проверка истечения/активации |
| refill | RefillPersonRanksCommand, полный административный пересчёт |

Generic bulk/atomic jobs не имеют production caller, но остаются публичным путём:
принимают reason, default manual, bulk передаёт atomic, atomic передаёт command.
Старые serialized jobs не содержат reason. Настоящий default свойства manual
инициализируется при unserialize старого payload; default параметра конструктора нет.

Rationale: причины различают обработку результатов, очистку и смену спортсмена.
Не делать вывод о причине из userId, criteria или текущего времени.
Исследование всех callers выполнено отдельным research agent по plan skill.

## Время и атомарность

Decision: Person.updateRanks получает reason и пишет оба metadata из Impression.
Оба реальных mutation paths найдены: RebuildPersonRanksService и
StandardEventPersonRankUpdater. Первый использует Clock.now при execution, второй
получает свежую Impression от UpdateEventRanksService. Queue impression используется
для автора, timestamp берётся заново service. Существующие transaction/lock и
repository сохранение projection/history не меняются.

Rejected: observer/save hook, updated_at как время расчёта, backfill по неизвестным
старым данным, присвоение до calculator, обновление только когда изменился rank.

## Read contract

Decision: rankCalculatedAt ISO8601 ATOM и rankCalculationReason string в
ViewPersonDto, оба Groups authenticated рядом с rankId. Используется тот же
serializer, что для created/updated, поля отсутствуют у guest. TypeScript Person
получает optional nullable timestamp и optional non-null reason union. UI не изменяется по ответу пользователя.

## Хранение

Decision: nullable datetime и NOT NULL varchar(32), enum cast в модели.
Временный default unknown заполняет старые записи, затем миграция удаляет default.
Person инициализирует Unknown для всех новых экземпляров. Дата остаётся null. Up/down не затрагивают current rank. Индекс не нужен,
по этим metadata нет query/filter сценария. DB-native enum отвергнут: усложняет
эволюцию списка, Domain enum и typed commands контролируют допустимые значения.

## Queue compatibility and Rector

Jobs сохраняют настоящий default свойства reason. Точечные исключения
ClassPropertyAssignToConstructorPromotionRector и
RemoveDefaultValueFromAssignedPropertyRector только для двух job classes
не позволяют удалить runtime default, необходимый старым serialized jobs.
Old-job regression проверяет исполнение payload без поля. Остальные правила
Rector активны, базовые типы не расширяются, global skips не добавлены.
