# Appearance contract

The SPA exposes one browser-local appearance state to its shell:

| State | Meaning | Root marker | Default |
|---|---|---|---|
| `light` | Existing light appearance | `.app-night-mode` absent | Yes |
| `night` | Dark appearance for the SPA shell and PrimeVue components | `.app-night-mode` on `document.documentElement` | No |

PrimeVue is configured with `theme.options.darkModeSelector: '.app-night-mode'` so its semantic tokens and overlays follow the same root marker.

The appearance control:

- is rendered in the global navigation for all visitors;
- toggles the state without navigation;
- exposes the current state with `aria-pressed` and a localized accessible label;
- persists only valid state values;
- falls back to `light` when storage cannot be read or written.
