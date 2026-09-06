# Research: SPA View Person

## Decision 1: Reuse the Person Info component

- Decision: Render PersonPromptPersonInfo at the top of the new page.
- Rationale: Payments and prompts already use this component, so person context, rank/club labels and impressions stay visually consistent.
- Alternatives considered: A second person header was rejected because it would duplicate API loading and create visual drift.

## Decision 2: Add a paginated ProtocolLine application query

- Decision: Add ListProtocolLines and ListProtocolLinesService, returning a Slice<ViewProtocolLineDto>.
- Rationale: Existing SPA lists use the same pagination headers and Slice; the current person Blade DTO eagerly loads the complete history and cannot support server-side filters.
- Alternatives considered: Extending ViewPersonDto with all protocol lines was rejected because it couples person info to a large, filterable collection.

## Decision 3: Use typed resources for relations

- Decision: Add ProtocolLineResources with event and competition flags. The query command translates withEvent=1 and withCompetition=1 into these resources.
- Rationale: This follows the repository/resource rule in the constitution and makes relation loading explicit. The person table requests both resources.
- Alternatives considered: Hidden eager-loading flags inside Criteria were rejected because they obscure query shape and make N+1 regressions harder to detect.

## Decision 4: Keep the existing public read policy

- Decision: Register the protocol-line list under optional-auth API middleware, matching public person/event reads. The new SPA page is public; mutation destinations retain their current auth guards.
- Rationale: The Blade View Person page is publicly viewable, while impressions are serialized according to the existing authenticated projection.
- Alternatives considered: Requiring authentication for the entire page was rejected because it would change the existing user-visible access policy.

## Decision 5: Preserve existing destinations

- Decision: Link competitions to /app/competitions/{competitionId}, events to /events/d/{distanceId}#{protocolLineId}, edit to /persons/{personId}/edit, ranks to /ranks/person/{personId}, and use SPA routes for payments/prompts.
- Rationale: This preserves working destinations while only migrating the View Person page itself.
- Alternatives considered: Adding new SPA event/rank/edit pages was rejected as outside this feature.
