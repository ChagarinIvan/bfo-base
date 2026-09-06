# Data Model: SPA-оплаты персоны

## Person

Существующая сущность, идентифицируемая personId.

- Используется для проверки существования и отображения person context.
- Фича не меняет person fields и lifecycle.
- Неизвестный или недоступный идентификатор даёт not-found состояние.

## PersonPayment

Существующая оплата персоны из persons_payments.

| Поле | Тип/форма | Правило |
|------|-----------|---------|
| id | string в API | Идентификатор записи |
| personId | string в API | Владелец оплаты |
| year | string в API | Год, извлечённый из date |
| date | Y-m-d | Обязательная дата оплаты |
| created | impression | Автор и момент создания |
| updated | impression | Автор и момент последнего изменения |

### Lifecycle

1. POST с person и датой ищет запись по personId + year.
2. Если записи нет, factory создаёт новую оплату.
3. Если запись есть и дата отличается, обновляется дата и updated impression.
4. Если дата совпадает, новая запись и лишнее обновление не создаются.

Уникальность пары person/year обеспечивается существующим application lock/query
поведением; схема и downstream queries не меняются.

## PersonPaymentForm

Transport/UI-сущность SPA:

- date: string в формате Y-m-d;
- pending: boolean;
- fieldErrors.date для 422;
- общий error state для 401/404/500.

Форма не хранит отдельный year: он вычисляется backend из date.
