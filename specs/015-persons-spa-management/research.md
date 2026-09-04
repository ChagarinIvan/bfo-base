# Исследование: SPA-управление персональными промптами

## Решения

### Существующий доменный контур переиспользуется

- Решение: сохранить `PersonPrompt`, `PersonPromptRepository`, существующие Application services,
  assembler, factory и phonetic calculation; добавить только недостающие API/pagination adapters.
- Обоснование: эти классы уже являются источником истины для prompt, metaphone, impression и lifecycle.
- Альтернативы: переписать prompt domain или использовать legacy `app/Services` — отклонены из-за риска
  расхождения с import/rank consumers и нарушения конституции.

### API повторяет paginated SPA-паттерн

- Решение: добавить auth-only `GET /api/v1/person-prompts?personId=...` с `Slice`, pagination headers и
  auth-aware serialization; mutation endpoints используют существующие Application commands/services.
- Обоснование: это совместимо с API клубов, соревнований, групп и текущими Axios/Vue моделями.
- Альтернативы: отдавать Blade или загружать все промпты без pagination — не покрывает SPA-контракт и
  создаёт неограниченную выдачу.

### Старые prompt routes удаляются полностью

- Решение: убрать старые web routes и prompt-only Blade actions/views; специальные redirects, 404
  handlers и заглушки не добавлять. Неизвестные URL обрабатываются Laravel по умолчанию.
- Обоснование: пользователь явно выбрал удаление старого интерфейса; shared consumers остаются.
- Альтернативы: redirect/dual UI — отклонены.

### Club person table получает явный режим контекста

- Решение: передать в `PersonTable` признак скрытия club column для `ClubDetailsPage`, не меняя
  глобальный список персон.
- Обоснование: колонка должна исчезнуть только в клубном контексте, остальные поля и actions должны
  остаться общими.
- Альтернативы: удалять club field из API или дублировать отдельную таблицу — ломает глобальный
  consumer или увеличивает дублирование.

### Конкурентность и stale responses

- Решение: сохранить transaction/lock для mutation, блокировать повторную submit в UI и применять
  request-id guard к paginated list loading.
- Обоснование: эти паттерны уже применяются в SPA и нужны для acceptance edge cases.

## Открытые ограничения, перенесённые в реализацию

- Фактические prompt validation/normalization rules не расширяются: `required|max:255` и существующая
  domain phonetic behavior остаются совместимыми; при тестах проверяются реальные текущие правила.
- Shared parser/rank/import consumers audited; legacy `PersonPromptService` удалён, а нужные сценарии
  переведены на Application-сервисы и repository contract.
- Новые API action/use-case classes размещаются в `Application / Domain / Bridge / Infrastructure`;
  `app/Services` не расширяется.
