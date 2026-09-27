# Quickstart: SPA night mode

1. Open `/app/competitions` as an anonymous visitor.
2. Confirm that the appearance control is visible beside the navigation actions even though Horizon is unavailable.
3. Activate the control and verify that the header, page background, a table or card, and PrimeVue controls use the night palette.
4. Activate it again and verify that the light palette returns without a route change or page reload.
5. Navigate to `/app/cups` and `/app/persons`; confirm that the selected mode remains active.
6. Refresh the browser and confirm that the selected mode is restored.
7. Clear the preference, reload, and confirm that light mode is the default.
8. Repeat the control checks while authenticated with Horizon access. Confirm that Horizon launch, logout, and route navigation remain unchanged.
9. Use Tab and Enter or Space to operate the control and inspect its accessible name and `aria-pressed` value in both modes.

## Verification commands

```bash
npm run test -- resources/spa/stores/appearance.test.ts resources/spa/components/AppLayout.test.ts
npm run typecheck
npm run build:spa
```
