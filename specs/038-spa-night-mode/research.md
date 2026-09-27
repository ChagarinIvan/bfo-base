# Research: SPA night mode

## Existing SPA theme integration

- The SPA initializes PrimeVue 4.2 with the Aura preset in `resources/spa/main.ts`.
- Aura exposes light and dark color schemes. PrimeVue supports a class based dark mode selector, which lets the application control the active scheme without replacing the preset.
- The existing shell also has custom CSS variables and hard-coded light surfaces in `resources/spa/styles.css`, so the feature needs a small application palette in addition to PrimeVue's scheme.

## Preference ownership

- The preference is browser-local. No API, account field, or server session is needed.
- A Pinia store is appropriate because the shell and tests can observe one reactive source of truth, while a small storage adapter keeps invalid or unavailable storage from breaking startup.
- The document root is the correct theme boundary because PrimeVue overlays and the global body background can be styled from it.

## Initial paint

- The stored preference should be applied during SPA bootstrap before mounting Vue. The app can also apply the class from the store when it hydrates, but bootstrap handling reduces a light flash for returning night-mode visitors.
- If storage is unavailable or contains an unknown value, light mode is the safe default.

## Control placement

- `AppLayout.vue` owns the global navigation and already renders the Horizon action conditionally.
- The appearance control belongs in the same `app-nav-auth` group, outside the Horizon visibility condition, so anonymous users and users without Horizon access can still use it.
- A native button with an icon, accessible label, and `aria-pressed` state fits the existing navigation controls and keyboard behavior.
