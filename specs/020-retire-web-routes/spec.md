# Feature Specification: Retire Remaining Web Routes

**Feature Branch**: `020-retire-web-routes`
**Created**: 2026-09-07
**Status**: Ready

## User Scenarios & Testing

### User Story 1 - Manage a person without Blade (Priority: P1)

An authenticated staff member deletes a person, extracts a new person from a protocol line, or assigns an existing person to a protocol line in the SPA; the result updates without visiting a legacy URL.

**Independent Test**: The JSON operations reject guests, mutate the expected records for an authenticated user, and SPA actions use them.

**Acceptance Scenarios**:

1. Given an authenticated staff member, when they confirm deletion, then the person is disabled and the SPA returns to the list.
2. Given an unmatched protocol line, when they extract a person, then the new person is created and the SPA opens it.
3. Given a legacy event page links to a protocol line, when staff choose a person in the SPA, then all equal protocol lines are assigned to that person.

### User Story 2 - Authenticate and register in the SPA (Priority: P1)

A user signs in, signs out, or an authenticated staff member sends a registration invitation without a Blade page; the recipient can activate the invitation link and receives their password email.

**Independent Test**: JSON endpoints cover login-adjacent flows and SPA routes expose the forms with their authorization rules.

**Acceptance Scenarios**:

1. Given an anonymous visitor, when they open login, then they use the SPA form and existing token login continues to work.
2. Given an authenticated staff member, when they submit an email, then an invitation is sent; activation handles invalid tokens safely.

### User Story 3 - Remove obsolete web entry points (Priority: P2)

Visitors do not encounter the retired Blade error, person, authentication, or registration pages; unknown SPA paths show an SPA not-found state.

**Independent Test**: The retired URLs return 404 and the SPA catch-all is reachable.

### Edge Cases

- A missing person or protocol line returns a JSON not-found response without partial changes.
- Failed invitation delivery or invalid activation token returns a safe error message and no password is changed.
- An unauthenticated request cannot perform any mutation.

## Requirements

- **FR-001**: Person deletion, extraction and protocol-line assignment use authenticated JSON operations and SPA actions.
- **FR-002**: Authentication, sign-out, registration invitation and invitation activation have SPA/API flows preserving current user-visible behavior.
- **FR-003**: New mutation actions use Application commands/services and aggregate events; no new legacy `app/Services` code is added.
- **FR-004**: Only the listed person, error, login, sign-in, sign-out and registration web routes, controllers, views, obsolete tests and now-unused `guest` middleware alias are removed. Their unreferenced action bases, error-mail path and supporting Blade templates are also removed; event and cup workflows remain.
- **FR-005**: The SPA has a not-found route for unmatched application paths.
- **FR-006**: All mutation APIs return JSON errors appropriate to unauthenticated, invalid and missing inputs.
- **FR-007**: API and SPA behavior is covered by regression tests.

## Success Criteria

- All listed legacy web routes are absent from the route table.
- Staff complete deletion, extraction and invitation flows without a Blade response.
- Unknown `/app/*` locations render a not-found SPA state.
- Targeted API and SPA tests pass.

## Assumptions

- Existing token-based SPA authentication remains the authentication mechanism.
- Invitation activation keeps the existing behavior: it creates or updates the account, generates a password, and emails it.
- Legacy event pages link protocol-line assignment to the SPA, so the assignment flow is migrated with the other listed person routes.

## Out of Scope

- Event and cup web routes, controllers and Blade workflows remain unchanged.
- Redesign of the registration policy or password-delivery mechanism.
