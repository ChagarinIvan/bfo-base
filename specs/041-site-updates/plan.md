# Implementation Plan: История обновлений сайта

**Branch**: `master` | **Date**: 2026-10-04 | **Spec**: [spec.md](spec.md)

**Input**: Authenticated static expandable history for every spec-kit feature and a lasting publication rule.

## Summary

Add `/app/updates` to the existing SPA auth guard and authenticated menu. Keep human-written Belarusian entries in a typed static module. Render date, summary, native accessible disclosure and useful SPA links. Backfill all 41 existing specs plus this one. Record a mandatory update step in the architecture manifest. No API or migration.

## Technical Context

**Language/Version**: TypeScript, Vue 3; PHP 8.5 / Laravel 13 only for existing delivery shell

**Primary Dependencies**: Vue Router, Pinia auth store, existing SPA styles and Belarusian translations

**Storage**: Static source file; no database or runtime fetch

**Testing**: Vitest component/router/data tests; repository quality gates

**Target Platform**: Existing browser SPA at `/app/*`

**Project Type**: Web application

**Performance Goals**: Render fewer than 100 static entries without network requests

**Constraints**: Reuse existing auth semantics; avoid links to removed routes; keyboard access and night theme

**Scale/Scope**: 42 initial records; one new route, one page, navigation, manifest

## Constitution Check

- Belarusian text in SPA and `resources/lang/by.json` for common UI labels: pass.
- SPA direction and no new Blade/API: pass.
- PrimeVue for interactive controls: use existing visual baseline with semantic `<details>` disclosure, a native browser control with keyboard support; no custom JS interaction needed. This matches existing navbar disclosures.
- No new backend aggregate, write operation, endpoint, or N+1 path: not applicable.
- Changed behavior covered by route, component and data tests: planned.
- Final CS/STAN/Rector/PHP and frontend gates: planned once after implementation.

Post-design recheck: no violations or new dependencies.

## Project Structure

### Documentation

```text
specs/041-site-updates/
├── spec.md
├── checklists/requirements.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/ui.md
├── quickstart.md
└── tasks.md
```

### Source Code

```text
resources/spa/pages/updates/updates.ts
resources/spa/pages/updates/UpdatesPage.vue
resources/spa/pages/updates/updates.test.ts
resources/spa/pages/updates/UpdatesPage.test.ts
resources/spa/router/index.ts
resources/spa/router/index.test.ts
resources/spa/components/AppLayout.vue
resources/spa/components/AppLayout.test.ts
resources/spa/components/navigationModels.ts
resources/spa/styles.css
resources/lang/by.json
.specify/memory/backend-architecture-manifest.md
```

**Structure Decision**: A static frontend catalog is enough for a read-only history; the existing route guard and layout provide access and navigation.
