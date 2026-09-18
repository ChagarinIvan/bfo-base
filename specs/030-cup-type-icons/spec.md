# Feature Specification: Cup Type Icons in SPA

**Feature Branch**: `030-cup-type-icons`

**Created**: 2026-09-18

**Status**: Draft

**Input**: Add a meaningful icon for every cup type in the SPA listing and cup
forms, with a reusable mapping for future event pages.

## User Scenarios & Testing

### User Story 1 - Recognize cup type in the listing (Priority: P1)

An SPA user can recognize the sport or age category of a cup from its icon next
to the cup name, without opening the cup.

**Why this priority**: The listing is the primary cup navigation surface and
the icon must provide immediate visual orientation.

**Independent Test**: Mount the cups listing with one cup of each enum type and
verify that each row renders the mapped icon and accessible label next to the
cup link.

**Acceptance Scenarios**:

1. **Given** a cup in the listing, **When** its type is rendered, **Then** the
   cup name is accompanied by the icon assigned to that type.
2. **Given** an icon-only visual affordance, **When** assistive technology reads
   the row, **Then** the type is available through an accessible label/title.
3. **Given** a public or authenticated listing, **When** cups are displayed,
   **Then** the same type-to-icon mapping is used in both modes.

### User Story 2 - Select a cup type with visual guidance (Priority: P1)

An authenticated administrator creating or editing a cup sees the same icon
beside every type option as in the listing and can submit the selected enum
value unchanged.

**Why this priority**: Consistent visual language reduces mistakes during
administrative create/edit flows.

**Independent Test**: Mount the shared cup form, inspect all type options,
select a type, submit, and verify the emitted payload and icon metadata.

**Acceptance Scenarios**:

1. **Given** the create or edit form, **When** the type selector opens, **Then**
   every supported `CupType` option has its corresponding icon and label.
2. **Given** an existing cup, **When** the edit form loads, **Then** its type
   option and icon are selected consistently with the listing.
3. **Given** a selected type, **When** the form submits, **Then** the enum
   string sent to the API is unchanged.

### User Story 3 - Reuse the mapping for future cup-related pages (Priority: P2)

Developers have one centralized, typed mapping that can be reused on future SPA
event pages when an event belongs to a cup. This feature does not add cup icons
to event pages yet.

**Why this priority**: Centralization prevents different pages from assigning
different meanings to the same cup type.

**Independent Test**: Unit-test the mapping for every current enum value and
verify that listing and form components consume the same mapping.

**Acceptance Scenarios**:

1. **Given** any current cup type, **When** the mapping is requested, **Then**
   it returns a stable icon class and human-readable label key.
2. **Given** a type not yet recognized by the SPA, **When** it is rendered,
   **Then** a neutral fallback icon and label are used without breaking the row
   or form.
3. **Given** a future event page, **When** it imports the mapping, **Then** it
   does not need to duplicate the cup-type rules.

### Edge Cases

- The backend enum gains a new value before the SPA mapping is updated; the UI
  must render a neutral fallback rather than an empty or broken icon.
- A cup type has a valid icon class but no loaded icon font; the accessible
  label/title remains available.
- The selected type is restored during edit loading and remains the submitted
  enum value.
- Icons are decorative supplements; the cup name and type label remain usable
  without color or icon recognition.

## Requirements

### Functional Requirements

- **FR-001**: The SPA MUST define one reusable mapping for every current backend
  `CupType` value to an icon class, label key, and neutral fallback.
- **FR-002**: The cups listing MUST render the mapped icon beside each cup name
  and retain the existing cup link behavior.
- **FR-003**: The create and edit cup type selectors MUST render the mapped icon
  beside every option while preserving the enum value submitted to the API.
- **FR-004**: Listing and form components MUST consume the same mapping rather
  than maintaining separate type-specific conditionals.
- **FR-005**: Every icon MUST have an accessible text alternative through a
  visible label, `aria-label`, or equivalent title.
- **FR-006**: Unknown type values MUST render a neutral fallback icon and label
  without throwing or hiding the cup row/form.
- **FR-007**: The implementation MUST use the existing SPA icon system or an
  approved bundled icon set; no remote icon assets are required.
- **FR-008**: Future event-page integration is explicitly out of scope for this
  feature, but the mapping MUST be importable by that future page.
- **FR-009**: Component/model tests MUST cover every current type, fallback,
  listing rendering, selector rendering, edit preselection, and submitted enum
  payload.
- **FR-010**: New user-facing text MUST be added only to the Belarusian
  dictionary.

### Key Entities

- **CupType icon definition**: Presentation metadata for one backend cup type:
  icon class, label key, and accessible fallback text.
- **Cup**: Existing cup DTO whose `type` value selects the icon definition.

## Success Criteria

### Measurable Outcomes

- **SC-001**: 100% of current backend cup types have a tested icon definition.
- **SC-002**: 100% of rendered listing rows and selector options expose an
  accessible type label in component tests.
- **SC-003**: Listing and create/edit forms use the same mapping module, with no
  duplicated type-to-icon conditionals.
- **SC-004**: An unknown type renders the fallback without a runtime error or
  loss of the cup name/value.
- **SC-005**: Existing SPA CI remains green and no API payload changes are
  introduced by the icon feature.

## Assumptions

- The backend `CupType` enum remains the source of valid values; this feature
  does not add or rename cup types.
- A bundled icon font already present in the project may be used; remote
  requests and new backend fields are out of scope.
- The shared mapping is a frontend presentation concern and does not require a
  database migration or API contract change.
- The existing Belarusian translation system remains the only SPA translation
  source.
- Event-page usage will be implemented in a later feature.

## Out of Scope

- Adding icons to event pages.
- Changing the backend `CupType` enum or persisted cup data.
- Redesigning the cup listing, forms, or action controls beyond adding the icon
  and its accessible label.
