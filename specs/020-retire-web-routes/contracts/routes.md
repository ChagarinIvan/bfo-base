# Contracts

- `DELETE /api/v1/persons/{personId}` — authenticated; disables a person; 204.
- `POST /api/v1/protocol-lines/{protocolLineId}/extract-person` — authenticated; returns the extracted person.
- `PUT /api/v1/protocol-lines/{protocolLineId}/person` — authenticated; accepts `personId`; assigns every equal line; 204.
- `POST /api/v1/auth/registration-invitations` — authenticated; accepts `email`; 204.
- `POST /api/v1/auth/registration-activation/{token}` — public; activates invitation; 204 or validation error.
- SPA: `/app/login`, `/app/registration`, `/app/registration/activate/:token`, `/app/protocol-lines/:protocolLineId/person`, and unmatched `/app/*`.
