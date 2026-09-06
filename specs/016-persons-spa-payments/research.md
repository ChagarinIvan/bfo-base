# Исследование: SPA-оплаты персоны

## Решения

### Существующий payment domain/application flow переиспользуется

- Решение: сохранить PersonPayment, PersonPaymentRepository,
  StandardPersonPaymentFactory и CreateOrUpdatePersonPaymentsService.
- Обоснование: текущий сценарий уже фиксирует год из даты, блокирует запись по
  person/year и обновляет только дату при повторной оплате за год.
- Альтернатива: добавить отдельный SPA-specific payment service — отклонена,
  поскольку это раздвоило бы domain behavior.

### API и SPA сохраняют auth-only payment-раздел

- Решение: GET и POST /api/v1/persons/{personId}/payments проходят через required
  authentication; SPA list/create routes также требуют auth.
- Обоснование: legacy payment routes уже находятся под auth middleware. Перенос не
  должен расширять доступ к финансовому статусу персоны.
- Альтернатива: сделать список public — нарушает существующую security semantics.

### Список использует общий pagination-контракт

- Решение: использовать общий `Slice`/pagination contract и фильтр `year`.
- Обоснование: payment SPA использует тот же paginated-list UX, что и остальные
  SPA-страницы, а year filter должен работать без загрузки всей истории.
- Альтернатива: оставить непагинированный endpoint — расходится с общим SPA
  контрактом и не даёт переиспользовать единый фильтр списка.

### Active-person фильтрация выполняется в payment repository

- Решение: `EloquentPersonPaymentRepository` присоединяет `person` и фильтрует
  `person.active = true`, как prompt repository.
- Обоснование: query-контур не должен работать с неактивными персонами и не должен
  раскрывать эту проверку в list application service.
- Альтернатива: отдельный `PersonRepository` check в list service — добавляет
  лишний запрос и расходится с prompt query-паттерном.

### Legacy cleanup выполняется после usages-аудита

- Решение: удалить только payment-only Blade routes/actions/views и заменить
  ссылку из person details на SPA route; payment repository/model/factory и
  ProtocolLines/rank consumers сохранить.
- Обоснование: persons_payments участвует в eligibility и ranking queries.
- Альтернатива: удалить весь payment namespace — нарушает shared flows.

## Ограничения реализации

- Existing date validation (required|date_format:Y-m-d) остаётся источником истины.
- DTO/command boundaries не переносят transport DTO наружу Application command.
- Application/Domain unit tests mock repository/collaborators; реальные записи
  создаются только в API/request integration tests.
