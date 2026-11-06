# PersonView contract

Существующий ViewPersonDto в detail/list/create/update/extract responses расширяется
двумя полями рядом с rankId. Нового endpoint и нового request параметра нет.

Authenticated пример:

```json
{"id":"42","rankId":6,"rankCalculatedAt":"2026-11-01T12:34:56+00:00","rankCalculationReason":"event_results_processed"}
```

Для ещё не рассчитанной Person оба поля null. Для guest оба ключа отсутствуют,
rankId и остальные публичные поля сохраняются. Поля read-only и не принимаются
как пользовательский источник reason. Enum string: manual, event_results_processed, event_results_cleared, protocol_person_changed, daily, refill.
Историческое protocol остаётся допустимым ответом и поддерживается старыми заданиями.
TypeScript Person объявляет поля optional nullable для guest/auth контракта.
Время соответствует последнему успешному execution, reason соответствует таблице
research.md; API POST rebuild использует manual, status 204 не меняется.
