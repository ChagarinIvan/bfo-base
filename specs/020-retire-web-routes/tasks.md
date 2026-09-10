# Tasks: Retire Remaining Web Routes

- [X] T001 [US1] Add API request tests for delete, extraction and assignment in `tests/Feature/Api/V1/Person/`.
- [X] T002 [US1] Refactor extraction and assignment into Application commands/services and add JSON actions/routes in `app/Application/Service/Person/` and `app/Bridge/Laravel/Http/Controllers/Api/V1/Person/`.
- [X] T003 [US1] Replace deletion, extraction and assignment SPA legacy links in `resources/spa/`.
- [X] T004 [US2] Add registration/activation API request tests and actions in `tests/Feature/Api/V1/Auth/` and `app/Bridge/Laravel/Http/Controllers/Api/V1/Auth/`.
- [X] T005 [US2] Add SPA registration and activation pages/routes in `resources/spa/pages/auth/` and `resources/spa/router/`.
- [X] T006 [US3] Add SPA not-found page and remove only listed person/error/authentication/registration web routes/controllers/views/tests plus their unreferenced action bases/error-mail path/supporting templates and the now-unused `guest` middleware alias in `resources/spa/`, `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php` and `app/Bridge/Laravel/Http/Kernel.php`; preserve event and cup flows.
- [X] T007 Run focused and final quality checks; mark completed work in this file.
