# API Contract: SPA View Person

All paths are under /api/v1 and use the existing JSON serializer and pagination headers.

## View person

GET /persons/{personId}

- Auth: existing optional API authentication.
- 200: existing ViewPersonDto; authenticated responses include impression projections.
- 404: unknown or inactive person.

## List protocol lines for a person

GET /protocol-lines?personId={int}&withEvent=1&withCompetition=1&year={YYYY}&competitionName={string}&date={Y-m-d}&page={int}&perPage={int}

- Auth: existing optional API authentication.
- personId: required positive numeric value.
- withEvent, withCompetition: optional boolean flags; the SPA sends both as 1.
- year: optional four-digit event year.
- competitionName: optional case-insensitive substring, at least three characters after trimming.
- date: optional exact event date in Y-m-d.
- Unknown or inactive person: 200 with an empty list; `personId` remains an active-person filter in the repository.
- Invalid query values: 422 with standard field errors.
- Success: paginated JSON array and X-Pagination-* headers.

Each item contains:

    {
      "id": "101",
      "personId": "7",
      "firstname": "Ivan",
      "lastname": "Runner",
      "distanceId": "42",
      "eventId": "9",
      "competitionId": "3",
      "competitionName": "Spring Cup",
      "eventName": "Long",
      "eventDate": "2026-05-10",
      "groupName": "M21",
      "year": "1990",
      "time": "00:42:13",
      "place": "4",
      "completeRank": "II"
    }

The repository query loads the requested event and competition resources before the slice is serialized. Query-count coverage must prove that relation loading does not create one query per returned line.

## SPA route

/app/persons/{personId} renders the public View Person page. It uses the existing auth guard only for destinations that require authentication:

- edit: /persons/{personId}/edit;
- prompts: /app/persons/{personId}/prompts;
- payments: /app/persons/{personId}/payments;
- ranks: /ranks/person/{personId}.
