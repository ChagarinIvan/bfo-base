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