# Quickstart

Use only bfo_base_test for tests. Run migration locally, add manual I with recent dates,
check rank/history. Rebuild twice and refill: same dates/source and result. Add junior without
birthday: survives. Add protocol fact and verify mixed extension/promotion. Delete manual source:
projection updates and source stays inactive. Guest writes 401; source from another Person 404.
Verify future activation via daily job, rollback after source persistence, duplicate 409.
UI in /app/persons/{id}/ranks: add, source label, delete confirm, no fake event links.
Run narrow backend/component tests during phases, full PHP/frontend and quality gates once at end.

Downgrade: down удаляет ручные источники и их projection rows. После rollback миграции
и возврата предыдущего кода обязательно выполнить persons:ranks:refill для пересчёта
Person.current_rank по оставшимся протоколам; ручные данные при downgrade теряются.
