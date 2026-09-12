# BFO Base API reference

Use the closest bounded-context example when conventions evolve.

## Canonical files

- Routes: `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- Action boundary: `app/Bridge/Laravel/Http/Controllers/ApiAction.php`
- DTO base: `app/Application/Dto/AbstractDto.php`
- JSON serializer: `app/Bridge/Laravel/Http/Serialization/ApiDtoSerializer.php`
- Error mapping: `app/Bridge/Laravel/Http/Serialization/ApiErrorResponse.php` and `app/Application/Exception/HttpError.php`
- Mutation examples: `app/Bridge/Laravel/Http/Controllers/Api/V1/Event/CreateEventAction.php`, `UpdateEventAction.php`, `UniteEventsAction.php`
- List example: `app/Bridge/Laravel/Http/Controllers/Api/V1/Event/ListEventsAction.php`
- Request contract tests: `tests/Feature/Api/V1/Event/EventManagementActionTest.php`

## Create action shape

```php
#[ResponseStatus(201)]
final class CreateEventAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        int $competitionId,
        EventDto $event,
        EventProtocolDto $protocol,
        AddEventService $service,
        UserId $userId,
    ): ViewEventDto {
        return $service->execute(new AddEvent($competitionId, $event, $protocol, $userId));
    }
}
```

The key properties are explicit route identity, transport DTOs only at the Bridge boundary, one command into the service, and a typed Application response.

## List action shape

```php
public function __invoke(
    SearchEventDto $search,
    Pagination $pagination,
    ListEventsService $events,
): Slice {
    return $events
        ->execute(new ListEvents($search))
        ->setPerPage($pagination->perPage)
        ->setCurrentPage($pagination->page);
}
```

The service builds criteria from command data, asks the repository for required `EventResources`, and maps models through an assembler. Pagination stays at the API boundary.

Resource flags describe the returned data, not request filters: use names such as `withCompetitionName`, `withDistances`, and `withProtocolLines`. A filter such as `withCompetition` belongs to the search/criteria contract and must not leak into the resource name.

For unite-like writes, the Application transaction locks source events, the standard factory creates the new aggregate, the repository persists it, and only then does a dedicated domain data service generate distances and protocol lines. This ordering is required because dependent rows need the new aggregate identifier.

## Protocol/domain input flow

1. DTO validates presence and carries raw transport data.
2. Command exposes a domain source/value object getter.
3. Application service invokes the domain factory and translates a domain exception.
4. The API layer serializes the annotated Application exception as a stable error response.

The action must not instantiate the domain source or call a downloader/parser.

## Route identity rule

For `competitions/{competitionId}/events`, `competitionId` is an action argument and command constructor argument. It is not part of `EventDto` validation or `fromArray()`. Apply the same rule to `{eventId}`, `{personId}`, and every other placeholder.

## Response visibility

View DTOs expose ordinary public readonly properties. Mark fields that are only for authenticated clients with `#[Groups(['authenticated'])]`; `ApiAction` chooses `public` or `authenticated` automatically.

## Error shape

Validation returns HTTP 422:

```json
{"errors":[{"code":"validation_error","field":"name","message":"..."}]}
```

Application failures use the same top-level `errors` array with their stable `HttpError` code. Domain exceptions do not contain HTTP status or transport codes.
