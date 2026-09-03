# Инвентаризация legacy-разрядов

Документ обновляется по мере переключения usages на проекцию `Person` и
`PersonRankHistory`. Целевое состояние: разряд вычисляется только из
идентифицированных `protocol_lines`; `app/Services` и старый Eloquent aggregate
не являются частью нового пути.

## Решения

| Legacy-точка | Решение | Целевой путь | Статус |
|---|---|---|---|
| `app/Services/RankService.php` | удалить | `RankCalculator` + `RebuildPersonRanksService` | удалено |
| `app/Repositories/RanksRepository.php` | удалить | `PersonRepository` для owned state/history | удалено |
| `app/Domain/Rank/Rank.php` и `app/Domain/Rank/RankRepository.php` | заменить aggregate enum-ом и удалить контракт | integer-backed enum `Domain\\Rank\\Rank` | выполнено |
| `app/Infrastructure/Laravel/Eloquent/Rank/EloquentRankRepository.php` | удалить после миграции Blade history | инфраструктурный Person persistence | удалено |
| `app/Application/Service/Rank/ActivePersonRankService.php` | удалить | чтение `Person.current_rank` | удалено |
| `app/Application/Service/Rank/RefillPersonRanksService.php` | перевести и удалить оболочку | `RebuildPersonRanksService` + `persons:ranks:refill {userId}` | удалено |
| `app/Application/Service/Rank/ActivateRankService.php` | удалить и перевести | `Application/Service/Person/ActivatePersonRankService` + `ProtocolLine.activateRank()` + rebuild | выполнено |
| `app/Application/Service/Rank/UpdateRankActivationDateService.php` | удалить и перевести | `Application/Service/Person/UpdatePersonRankActivationDateService` + rebuild | выполнено |
| `app/Application/Handler/Rank/ProtocolLineRankActivatedHandler.php` | перевести | rebuild владельца source line | выполнено |
| `app/Jobs/RefillPersonRankJob.php` | удалить | `Bridge\\Laravel\\Jobs\\RebuildPersonRanksJob` | удалено |
| `app/Bridge/Laravel/Console/Commands/RecalculatingRanks.php` и `ReFillPersonRanksCommand.php` | перевести | безопасный full refill command | новый `RefillPersonRanksCommand`, старый файл удалён |
| `app/Bridge/Laravel/Console/Commands/IdentProtocolLineCommand.php` | адаптировать | rebuild через `RebuildPersonRanksService` | выполнено |
| `app/Bridge/Laravel/Console/Commands/ExportPersonsCommand.php` | адаптировать | чтение materialized rank | выполнено |
| `app/Bridge/Laravel/Http/Controllers/Person/ShowPersonAction.php` и `app/Bridge/Laravel/Http/Resources/PersonResource.php` | адаптировать | `Person.current_rank`/новый DTO | выполнено |
| `app/Bridge/Laravel/Http/Controllers/Rank/*` | сохранить историю, остальное удалить/перевести | новый read use case и rebuild | старый список/check/export/refill удалены |
| `resources/views/ranks/*`, navbar и translations | сохранить только поддерживаемую историю | `ViewPerson` с `withRanksHistory` и eager-load | выполнено; старый список убран из navbar |
| `app/Repositories/ProtocolLinesRepository.php` | не использовать в новом rank flow | `RankFactsCollector` + `EloquentRankFactsCollector` | остаётся legacy dependency `ProtocolLineService`, вне rank flow |
| `app/Infrastructure/Integration/OrientBy/OrientBySyncService.php` | адаптировать | внешний rank игнорируется без protocol line | выполнено |
| `app/Models/Parser/*` | сохранить парсеры, заменить строковую валидацию | нормализация в `Rank` enum | выполнено |

## Правило совместимости

Старые расчёт, persistence, список и refill-вход удалены. Сохранены только Blade-
страницы истории и ручной активации, которые используют `PersonRankHistory` и
единый rebuild path. Полный refill выполняется консольной командой.
