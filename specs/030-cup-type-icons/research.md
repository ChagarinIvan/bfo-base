# Research: Cup Type Icons in SPA

## Existing UI and assets

- `resources/spa/main.ts` loads PrimeIcons but not Font Awesome.
- `@fortawesome/fontawesome-free` is already a project dependency and its CSS
  is used by the legacy application.
- `CupsPage.vue` renders the cup name as a link and `CupForm.vue` renders type
  options through PrimeVue `Select`.

## Icon selection

Use bundled Font Awesome classes because the required visual concepts are
available without adding dependencies or remote assets:

| Cup type | Icon | Meaning |
|---|---|---|
| elite | `fa-running` | adult running |
| master | `fa-user-tie` | veteran |
| sprint | `fa-bolt` | sprint/speed |
| bike | `fa-bicycle` | bicycle |
| juniors | `fa-user-graduate` | junior |
| youth | `fa-child` | youth |
| new_youth | `fa-child` | youth |
| new_master | `fa-user-tie` | veteran |
| ski | `fa-skiing` | skiing |
| elk_path | bundled SVG moose | elk path |

The label remains the localized type name; icon classes and illustrations are
visual metadata. Unknown values use `fa-question-circle` and a generic
Belarusian label.

## Decision

Create a shared model and `CupTypeIcon` component. Do not add an API field or
persist icon metadata: the backend enum remains authoritative and the frontend
mapping is the presentation layer. Use Select slots for type options so the
selected value remains the existing enum string.
