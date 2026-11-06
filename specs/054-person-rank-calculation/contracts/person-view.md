# PersonView contract

Существующий ViewPersonDto в detail/list/create/update/extract responses расширяется
двумя полями рядом с rankId. Нового endpoint и нового request параметра нет.

Authenticated пример:

```json
{"id":"42","rankId":6,"rankCalculatedAt":"2026-11-01T12:34:56+00:00","rankCalculationReason":"event_results_processed"}
```

Для ещё не рассчитанной Person дата null, причина unknown. Для guest оба ключа отсутствуют,
rankId и остальные публичные поля сохраняются. Поля read-only и не принимаются
как пользовательский источник reason. Enum string: unknown, manual, event_results_processed, event_results_cleared, protocol_person_changed, daily, refill.
Историческое protocol остаётся допустимым ответом и поддерживается старыми заданиями.
TypeScript Person объявляет timestamp optional nullable, reason optional non-null для guest/auth контракта.
Время соответствует последнему успешному execution, reason соответствует таблице
research.md; API POST rebuild использует manual, status 204 не меняется.

rankCalculationReason всегда string для authenticated, включая unknown до первого
расчёта. rankCalculatedAt остаётся nullable; у guest оба поля отсутствуют.
