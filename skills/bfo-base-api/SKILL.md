---
name: bfo-base-api
description: Create, extend, or review JSON API v1 endpoints in the BFO Base project style. Use for every new or materially changed API endpoint in this repository; do not use it for Blade/web-only controllers or unrelated external APIs.
---

# BFO Base JSON API

Use this skill for every new or materially changed JSON API endpoint in this repository. Preserve the existing transport boundary and Application/Domain architecture instead of introducing a generic Laravel REST pattern.

## Before coding

1. Read `CLAUDE.md`, the constitution, and the active feature artifacts in `specs/`.
2. Inspect the closest endpoint in the same bounded context: route, action, request DTO, command, Application service, assembler/view DTO, exception mapping, and tests.
3. Check `git status` and preserve unrelated user changes.
4. Confirm the contract: verb/path, authentication middleware, route parameters, body/query fields, success status, response DTO, pagination, and error codes.

## Layer boundary

Use this request path:

`route -> ApiAction -> Application command -> Application service -> Domain ports/aggregate -> assembler/view DTO -> ApiDtoSerializer`

- Register routes in `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php` under the existing `api/v1` middleware groups.
- Put actions in `app/Bridge/Laravel/Http/Controllers/Api/V1/<Context>/`.
- Actions extend `BaseController`, use `ApiAction`, and contain no domain logic, Eloquent access, parsing, repository calls, or manual JSON serialization.
- An action receives explicit scalar route arguments (for example `int $competitionId` or `string $eventId`), transport DTOs, `UserId` where authenticated, and one Application service. It constructs the command and returns the service result.
- Route identity belongs to route parameters, never duplicated body DTO fields.
- Application services expose `execute(Command $command)`. Commands are the single Application input and may expose getters that create domain value objects.
- Actions must not construct domain values such as `ProtocolSource`, `Criteria`, `EventInput`, or `Protocol`.
- Domain services, factories, aggregates, repository interfaces, and domain exceptions stay below Application. Infrastructure implements repository ports; do not add new legacy `app/Services` or `app/Repositories` code.

## Request DTOs

- Request DTOs extend `App\Application\Dto\AbstractDto` and contain only HTTP body/query/form data.
- Keep DTOs as data plus validation/normalisation (`requestValidationRules()`, `parametersValidationRules()`, `normaliseRequestData()`, `fromArray()`). Do not download files, parse protocols, call repositories, or make domain decisions in a DTO.
- Split transport concerns when inputs are conceptually separate (for example event information and protocol source). Validation belongs in the DTO; domain resolution belongs in a factory/service.
- For upload-or-URL inputs, let the DTO carry raw data, let the command expose a domain source/value object, and let a domain factory resolve it.
- Use `Pagination` plus a search DTO for list endpoints. Return `Slice<View...Dto>` and let the action apply `setPerPage()`/`setCurrentPage()`.

## Commands and services

- Name commands after use cases (`AddEvent`, `UpdateEvent`, `DisableEvent`, `UniteEvents`, `ListEvents`) and services after the Application operation (`AddEventService`, etc.).
- A mutation service owns orchestration and transaction boundaries. Inject repositories, factories, assemblers, and domain services through constructors/interfaces.
- Reusable business rules belong in Domain `*Factory`, `*Updater`, aggregate methods, or domain services. Application services orchestrate and translate transport data.
- If a mutation has independent domain concerns, split them into separate domain methods/events/handlers.
- Build repository `Criteria` from command getters and pass typed `*Resources` for eager relations. Do not hide eager-loading flags inside criteria.
- Name resource flags for the returned API/application data (`withCompetitionName`, `withDistances`, `withProtocolLines`); keep them distinct from search/filter fields such as `withCompetition`.
- For internal unpaginated reads, use a dedicated Application service/command backed by repository `byCriteria`; do not fake pagination with a huge API limit.
- For unite-like mutations, lock selected source records inside the transaction, create the aggregate through the standard domain factory, persist it with the repository before generating dependent relations, then run the dedicated relation/data service.

## Responses and errors

- Return public readonly view DTOs (`View...Dto`) or `Slice<View...Dto>` from Application services. Do not return Eloquent models from actions.
- Use `#[ResponseStatus(201)]` for creates and `response()->noContent()` for delete/deactivate endpoints when that is the contract.
- Let `ApiAction` serialize via `ApiDtoSerializer`; do not hand-build JSON for the new Application API style unless an existing contract requires it.
- Hide authenticated-only DTO properties with `#[Groups(['authenticated'])]`.
- Expected failures are Application exceptions annotated with `#[HttpError(status: ..., code: ...)]`. Domain exceptions contain no HTTP knowledge; Application translates them.
- Validation remains HTTP 422 in the existing field-error format. Use stable machine-readable codes for other failures (`*_not_found`, `invalid_protocol`, etc.).

## Tests and verification

- Application/Domain unit tests mock repositories and collaborators; assert command payloads, criteria/resources, transaction calls, factory calls, and error translation. Do not instantiate Eloquent models in these tests.
- Feature/API request tests use real database records and verify authentication, route parameter handling, validation status/fields, response shape/status, and important persistence effects.
- Update serializer/assembler tests when response DTOs or visibility groups change.
- Run focused PHP tests and `composer cs -- --sequential` while iterating. At feature completion run the requested final gate and record environmental blockers instead of weakening the contract.

## Review checklist

- route is in the correct auth group and uses the intended verb/path;
- every route placeholder is an explicit action parameter passed into the command;
- DTOs contain no route identity and only validate/normalise transport data;
- action has no domain, repository, Eloquent, parsing, or response-format logic;
- service accepts one command and returns the contract's view DTO/slice/void;
- domain values are created in the command or Application service, not in the action;
- expected domain failures map to Application HTTP errors;
- criteria and resources are separate, and pagination is applied at the API boundary;
- success/error/authentication/validation behaviour has focused tests;
- feature spec, plan, contracts, and tasks reflect the delivered API behaviour.

## Current examples

Read [references/api-patterns.md](references/api-patterns.md) for concrete examples from this repository before implementing or reviewing an endpoint.
