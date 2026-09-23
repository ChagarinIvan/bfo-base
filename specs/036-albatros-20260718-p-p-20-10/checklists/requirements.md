# Specification Quality Checklist: `п.п. 20.10`

## Content Quality

- [x] Описан реальный fixture и наблюдаемая production-жалоба.
- [x] User stories ориентированы на корректный импорт результатов.
- [x] Acceptance scenarios проверяемы через public parser flow.
- [x] Scope ограничен parser/import flow.

## Requirement Completeness

- [x] Зафиксированы варианты написания маркера и Windows-1251 input.
- [x] Требования различают обычное время и отсутствие результата.
- [x] Указаны измеримые критерии: 237 строк, четыре representative cases.
- [x] Старые fixtures и совместимость включены.
- [x] Контракт, data-model и quickstart согласованы.

## Implementation Readiness

- [x] План содержит red/green порядок.
- [x] Тестовые кейсы включают женскую, мужскую и Open-группы.
- [x] Не требуется изменение БД, API или SPA.
