# Tasks: Cup Event SPA Forms

## Dependencies

`US1 → US2 → US3`; backend contract tasks precede SPA consumers. Polish runs
after all stories.

## Phase 1: Setup

- [X] T001 Verify existing ignore files and target CupEvent module boundaries in `.gitignore` and `app/{Application,Domain,Infrastructure}/CupEvent`

## Phase 2: Foundational API model

- [X] T002 Add validated CupEvent form DTO in `app/Application/Dto/CupEvent/CupEventDto.php`
- [X] T003 Add CupEvent input/factory and repository add operation in `app/Domain/Cup/CupEvent/{Factory,CupEventRepository.php}` and `app/Infrastructure/Laravel/Eloquent/CupEvent/EloquentCupEventRepository.php`
- [X] T004 Add target create, view and update commands/services in `app/Application/Service/CupEvent/`
- [X] T005 Add missing CupEvent application exceptions in `app/Application/Service/CupEvent/Exception/`

## Phase 3: User Story 1 — Add a stage (P1)

**Goal**: An authenticated administrator can create a stage in the SPA.

**Independent test**: Create a stage with valid data and see it on the cup page; invalid and guest requests fail correctly.

- [X] T006 [US1] Add create form V1 action and protected route in `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/CreateCupEventAction.php` and `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- [X] T007 [P] [US1] Add API create/validation/guest request coverage in `tests/Feature/Api/V1/Cup/CupEventFormActionsTest.php`
- [X] T008 [US1] Add CupEvent form endpoint client/types in `resources/spa/api/{cups.ts,types.ts,cups.test.ts}`
- [X] T009 [US1] Add shared SPA stage form and create page in `resources/spa/pages/cups/{CupEventForm.vue,CreateCupEventPage.vue}`
- [X] T010 [US1] Add protected create route and cup-card action target in `resources/spa/router/index.ts` and `resources/spa/pages/cups/CupViewPage.vue`
- [X] T011 [P] [US1] Add SPA create/form regression coverage in `resources/spa/pages/cups/{CupEventForm.test.ts,CreateCupEventPage.test.ts,CupViewPage.test.ts}`

## Phase 4: User Story 2 — Edit a stage (P1)

**Goal**: An authenticated administrator can load and edit only a stage of its cup.

**Independent test**: Load an existing stage, update it, return to its cup; mismatched cup/stage shows not found.

- [X] T012 [US2] Add V1 view/update actions and routes in `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/{ViewCupEventAction,UpdateCupEventAction}.php` and `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- [X] T013 [P] [US2] Extend API ownership/view/update request coverage in `tests/Feature/Api/V1/Cup/CupEventFormActionsTest.php`
- [X] T014 [US2] Add edit API client and SPA page in `resources/spa/api/cups.ts` and `resources/spa/pages/cups/EditCupEventPage.vue`
- [X] T015 [US2] Add protected edit route and table action target in `resources/spa/router/index.ts` and `resources/spa/pages/cups/CupViewPage.vue`
- [X] T016 [P] [US2] Add SPA edit/not-found regression coverage in `resources/spa/pages/cups/EditCupEventPage.test.ts` and `resources/spa/pages/cups/CupViewPage.test.ts`

## Phase 5: User Story 3 — Retire legacy forms (P2)

**Goal**: SPA is the sole create/edit UI while unrelated legacy operations remain.

- [X] T017 [US3] Remove superseded CupEvent Blade form controllers/actions/templates in `app/Bridge/Laravel/Http/Controllers/CupEvents/` and `resources/views/cup/events/`
- [X] T018 [US3] Remove legacy create/edit routes/imports and controller tests in `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php` and `tests/Bridge/Laravel/Http/Controllers/CupEvents/`
- [X] T019 [US3] Verify legacy form-route retirement and retained delete route in `tests/Feature/Api/V1/Cup/CupEventFormActionsTest.php`

## Phase 6: Polish and verification

- [X] T020 Cover domain-to-application error mapping without Eloquent factories in `tests/Application/Service/CupEvent/AddCupEventServiceTest.php`
- [X] T021 Run formatter, static analysis, rector and frontend CI; record local database limitation in `specs/031-cup-event-spa-forms/quickstart.md`

## Implementation Strategy

Deliver the V1 create contract first, then the shared form and create route.
Add the edit API and page on the same shared form, remove legacy paths only
after both SPA routes are covered, and finally run all quality gates.
