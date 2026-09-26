# Feature Specification: Retire Legacy Web Routes and Services

**Feature Branch**: `039-retire-legacy-web`  
**Created**: 2026-09-26  
**Status**: Implemented
**Input**: Remove all `WebRoutesServiceProvider` routes and the provider; migrate cup exports and remaining actions to V1 API; remove obsolete frontend code, `CupEventsService`, and `DistanceService`; audit other legacy services for small safe removals.

## User Scenarios & Testing

### User Story 1 - Manage cups through the SPA (Priority: P1)

An authenticated editor clears all calculated cup tables, disables a cup, or disables a cup stage from the SPA. Each action reports success or an actionable error without opening an old page.

**Why this priority**: These are the remaining active links to legacy cup URLs.

**Independent Test**: Perform each action in the SPA as an editor, repeat as a guest, and inspect the resulting cup and table.

**Acceptance Scenarios**:

1. **Given** cached tables for multiple cups, **When** an editor clears the cup cache, **Then** the next table request for each cup recalculates current data and the SPA stays on the current cup.
2. **Given** an existing cup, **When** an editor disables it, **Then** it disappears from active cup lists and the SPA opens the cup list.
3. **Given** an existing stage, **When** an editor disables it, **Then** the cup view no longer lists it and its table is recalculated.
4. **Given** a guest or an unknown cup or stage, **When** they invoke an action, **Then** they receive the established authorization or missing-resource response and no data changes. Cache clear requires authentication but no cup ID.

### User Story 2 - Download cup tables (Priority: P1)

An authenticated user downloads the full cup table from the SPA. The exported rows and totals agree with the group tables shown in the SPA.

**Why this priority**: Export is the other active function of the legacy route provider.

**Independent Test**: Download the file for a cup with multiple groups, compare its rows with group table views, and exercise an unknown cup ID.

**Acceptance Scenarios**:

1. **Given** a cup with multiple groups, **When** a user exports the cup, **Then** the file contains each supported group, its stage results, ranked participants, and totals shown in the SPA.
2. **Given** an unknown cup, **When** a user exports, **Then** the API returns a JSON error and no file.
3. **Given** a guest, **When** they request the export, **Then** the API denies access.

### User Story 3 - Retire old frontend entry points (Priority: P2)

Users enter through the SPA. Retired web URLs, view components, and support code no longer handle user actions. The root URL opens the SPA's competition list.

**Why this priority**: Removing dead routes and pages prevents links from bypassing the API workflow.

**Independent Test**: Inspect registered routes and visit the root and former cup URLs.

**Acceptance Scenarios**:

1. **Given** a visitor, **When** they open `/`, **Then** they reach the competition list in the SPA.
2. **Given** any old cup action URL, **When** it is requested, **Then** it does not execute an action.
3. **Given** a live registration or password email, **When** it is rendered, **Then** its template remains available.

### User Story 4 - Keep distance and protocol behavior after cleanup (Priority: P2)

Cup scoring and event protocol updates continue to find, compare, and remove distances correctly after the legacy distance facade is removed. Maintainers receive a concrete inventory of other legacy service files that can be removed safely.

**Why this priority**: Both workflows call `DistanceService`; deleting it without replacing its behavior would corrupt standings or protocol updates.

**Independent Test**: Compare cup tables and protocol update effects before and after the migration, then review the service inventory against code references.

**Acceptance Scenarios**:

1. **Given** an event with groups sharing distance characteristics, **When** a cup table is calculated, **Then** the same eligible lines and scores appear.
2. **Given** an event protocol replacement or event disable, **When** cleanup runs, **Then** obsolete distances and lines are removed before new data is processed.
3. **Given** the legacy services directory, **When** the cleanup is reviewed, **Then** each service is classified as removed now or retained with its active callers and the reason larger refactoring is needed.

### User Story 5 - Resolve impression authors after web retirement (Priority: P1)

An authenticated user sees the author's email beside an impression in the SPA. The existing private `GET /api/v1/users` endpoint remains available after the old web layer is removed.

**Independent Test**: Open a record whose author is absent from the SPA's cached user list and inspect the author label after the user list refreshes.

**Acceptance Scenarios**:

1. **Given** an author present in the users API, **When** an authenticated user views an impression, **Then** the author label shows the email even when a name exists.
2. **Given** an author missing from the SPA's cached user list but present in the API, **When** the impression is shown, **Then** the SPA refreshes the list and replaces the numeric fallback with the email.
3. **Given** a guest, **When** a page is viewed, **Then** no user-list request is made to resolve impressions.

### Edge Cases

- A cup with no stages or no ranked participants produces a valid export with headings and no invented rows.
- Semicolons, quotes, or line breaks in names do not change the number of CSV columns.
- A stale or repeated cache-clear request remains safe.
- Cup cache invalidation remains safe when an event is linked to an inactive cup or a stage ID differs from its cup ID.
- A genuinely missing author retains the numeric fallback after a fresh users API response.
- The retired group export URL does not produce a file.
- Repeated distance matches do not duplicate protocol lines or cup points.

## Requirements

### Functional Requirements

- **FR-001**: Cup disable, stage disable, cache clear, and full export currently registered by `WebRoutesServiceProvider` MUST have V1 API equivalents. The unused group export and all old URL actions and provider MUST be removed.
- **FR-002**: Cup and cup-stage disabling and global cup cache clearing MUST use the existing authorization model and return API responses without redirects. The cache-clear route, Application service, and invalidator MUST NOT require a cup ID.
- **FR-003**: Full-cup export MUST be downloadable through an authenticated V1 API route, preserve CSV compatibility, and use the same calculated table source as the SPA table view.
- **FR-004**: Export failures MUST use the V1 JSON error contract for unauthenticated requests and unknown cups.
- **FR-005**: The SPA MUST call the new actions for cup management and export; no active SPA link may invoke a retired cup URL.
- **FR-006**: The root URL MUST continue to lead to `/app/competitions`; retired action URLs MUST no longer perform side effects.
- **FR-007**: Code used only by the old browser pages MUST be removed, including obsolete views and view components. Email templates still used for registration or password delivery MUST remain.
- **FR-008**: `CupEventsService` MUST be removed. Exports and table viewing MUST share the cup table builder and its cache.
- **FR-009**: `DistanceService` MUST be removed. Distance queries and deletion MUST remain behaviorally equivalent, with domain rules kept outside legacy services.
- **FR-010**: The feature MUST include a reference-backed inventory of the remaining `app/Services` classes and remove any additional class with no live production caller when that removal requires no larger refactoring.
- **FR-011**: Changed API, SPA, cup scoring, and protocol cleanup behavior MUST have focused automated coverage.
- **FR-012**: The existing authenticated `GET /api/v1/users` JSON array of `id`, `name`, and `email` MUST remain available to resolve impression authors. The SPA MUST display email when an author is found and refresh a stale cached user list when the author ID is absent. Guests MUST NOT fetch the user list for impressions.

### Key Entities

- **Cup**: Competition series with supported groups, stages, ranking rules, and cached tables.
- **Cup stage**: Event included in a cup; disabling it changes the table.
- **Cup table**: Ranked participants, stage cells, totals, and group labels used by view and export.
- **Distance**: Event course linked to groups and protocol lines; equal distances share length and points.

## Success Criteria

### Measurable Outcomes

- **SC-001**: Both cup disable actions, global cup cache clear, and the full export complete from the SPA without visiting a retired URL.
- **SC-002**: For a representative multi-group cup, exported ranked rows and totals match the displayed tables for every group.
- **SC-003**: All five retired cup URLs perform zero mutations, while the root URL still reaches the SPA competition list.
- **SC-004**: The legacy service inventory names every class in `app/Services` and gives a caller-based disposition.
- **SC-005**: Focused regression coverage passes for the changed behavior; the final feature quality gates pass.
- **SC-006**: An authenticated impression shows the author's email after a stale user-list cache is refreshed.

## Assumptions

- The existing V1 token/session authentication and SPA authorization apply to the new cup actions.
- An export is a CSV attachment delivered from a V1 API URL. Errors and state-changing responses follow the JSON API contract.
- Registration and password email templates are mail content, not old frontend pages.
- The root redirect remains a minimal framework route outside `WebRoutesServiceProvider`.
- This feature does not redesign cup scoring formulas or protocol parsing.
