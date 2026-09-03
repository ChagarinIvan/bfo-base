# CLAUDE.md

Инструкции для работы с этим репозиторием. Полный свод принципов — в
`.specify/memory/constitution.md` (конституция проекта); этот файл дублирует то,
что должно применяться в каждом запросе.

## Навигация и рефакторинг

При навигации по коду и рефакторинге предпочитаем инструменты `phpstorm-index` MCP
(поиск определений, поиск usages, переименование, иерархии) вместо grep/ripgrep.

## Рабочий процесс и архитектура

- Для новой функциональности сохраняем цикл `specify → review → plan → review →
  tasks → implement` и обновляем соответствующие артефакты в `specs/`.
- Новый код следует слоям `Application / Domain / Bridge / Infrastructure`.
  Новые сценарии оформляем Application-сервисами; `app/Services` — legacy-слой,
  его не расширяем. Новые репозитории не создаём, фасады Laravel не используем;
  зависимости передаём через конструктор и интерфейсы.
- Bridge actions формируют command; Application service принимает только command в `execute()`.
  Command не возвращает transport DTO: только primitive либо domain input/value object. Повторяемые
  mutation-правила живут в Domain `*Updater`/`*Factory`; aggregate `create`/`disable` фиксируют
  domain events, а repository `add()` вызывает aggregate method.
- Repository query API строится на `byCriteria`/`oneByCriteria`; required eager relations передаются
  typed `*Resources` из Application, а не флагами внутри Criteria. Normalized input нормализуется
  одним Domain normalizer до сравнения как при write, так и в parser/import flow.
- Перед правкой проверяем `git status` и сохраняем незакоммиченные изменения
  пользователя. Не переключаем ветки и не откатываем файлы без явного запроса.
- PHP 8.5 / Laravel 13; имена импортируем через `use`, inline-FQCN не пишем.
- Результат сверяем со спецификацией, acceptance scenarios, контрактами и
  checklist соответствующей фичи.

## Кодстайл

- **Импорт вместо FQCN.** Классы, интерфейсы, трейты, атрибуты, функции и
  константы подключаем через `use` и используем короткое имя. НЕ пишем полное
  имя с ведущим слэшем инлайн — ни в атрибутах `#[...]`, ни в тайпхинтах, ни в
  вызовах, ни в аргументах.

  ```php
  // Плохо
  #[\Illuminate\Database\Eloquent\Attributes\Scope]
  protected function active($query) { ... }

  // Хорошо
  use Illuminate\Database\Eloquent\Attributes\Scope;

  #[Scope]
  protected function active($query) { ... }
  ```

  Это уже закреплено в `.php-cs-fixer.php` (`global_namespace_import`,
  `fully_qualified_strict_types`), но пишем так сразу, а не полагаемся на
  автофикс.

## Стандарт модернизации (PHP 8.5 / Laravel 13)

- Пустые тела конструкторов и методов оформляем с фигурными скобками на отдельной строке:
  сигнатура заканчивается на `)`, затем на следующей строке `{`, тело и `}` каждый на своей строке.
  Это локальное правило проекта дополнительно проверяется на ревью, поскольку php-cs-fixer не имеет
  отдельного fixer-а для обязательного переноса пустого тела при однострочной сигнатуре.

- Современные конструкции — **норма**, закреплённая инструментами (принцип V «только вперёд»).
  `rector.php` применяет полный PHP-сет (`withPhpSets()` от composer floor `^8.5`), Laravel-сет
  (`UP_TO_LARAVEL_130`) и prepared-наборы (`deadCode`/`codeQuality`/`typeDeclarations`/`privatization`/
  `earlyReturn`/`instanceOf`/`if`); `.php-cs-fixer.php` — стиль-правила PHP 8.x. Новый код пишем в этом
  виде сразу; rector/cs-гейт приводит к нему автоматически.
- **Осознанно НЕ включены** (ломали код/давали шум, отсеяны гейтами): rector-группы `naming`
  (ренейм под тип → рассинхрон `@var`/Blade) и `namedArgs` (именованные аргументы там, где запрещены).
  `codingStyle` из rector не берём — стиль за php-cs-fixer (разделение ответственности).

## Экономия контекста Codex

- В промежуточных сообщениях и финальных ответах использовать краткие формулировки.
- Не выводить целые файлы и большие diff без явной необходимости; показывать только релевантные фрагменты.
- При поиске сначала читать только файлы, относящиеся к текущей задаче, и не повторять уже известный контекст.
- В Application/Domain unit-тестах не создавать Eloquent-сущности даже через `Factory::make()`:
  мокировать repository/assembler collaborators и проверять criteria, payload и вызовы. Реальные
  записи БД создавать только в integration/API request-тестах.
- Между фазами запускать только узкие тесты изменённого поведения; `composer test` и полный frontend
  CI — один раз в конце фичи либо по явному запросу.
- Не обращаться к web без необходимости; большие логи и содержимое файлов не дублировать в сообщениях.
