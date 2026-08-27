# CLAUDE.md

Инструкции для работы с этим репозиторием. Полный свод принципов — в
`.specify/memory/constitution.md` (конституция проекта); этот файл дублирует то,
что должно применяться в каждом запросе.

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

- Современные конструкции — **норма**, закреплённая инструментами (принцип V «только вперёд»).
  `rector.php` применяет полный PHP-сет (`withPhpSets()` от composer floor `^8.5`), Laravel-сет
  (`UP_TO_LARAVEL_130`) и prepared-наборы (`deadCode`/`codeQuality`/`typeDeclarations`/`privatization`/
  `earlyReturn`/`instanceOf`/`if`); `.php-cs-fixer.php` — стиль-правила PHP 8.x. Новый код пишем в этом
  виде сразу; rector/cs-гейт приводит к нему автоматически.
- **Осознанно НЕ включены** (ломали код/давали шум, отсеяны гейтами): rector-группы `naming`
  (ренейм под тип → рассинхрон `@var`/Blade) и `namedArgs` (именованные аргументы там, где запрещены).
  `codingStyle` из rector не берём — стиль за php-cs-fixer (разделение ответственности).