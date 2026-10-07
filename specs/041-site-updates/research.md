# Research: История обновлений сайта

## Decision: date provenance

Use the latest Git commit date touching each historical `spec.md` as a **documentation fixation date**, clearly labeled in the interface. For 041 use 2026-10-05, when this implementation was finished. These dates do not claim production deployment. A production release calendar was unavailable. Alternative: `Created`, absent in many specs and often predating delivery. A late edit may postdate delivery, so the label explicitly avoids a release claim.

## Decision: catalog and identity

Use the full directory slug, such as `030-cup-view-spa`, as unique ID. There are 41 current spec directories and duplicate numeric prefixes 030 and 034. Source records are curated instead of generated at runtime, so prose and optional examples and links can be reviewed. Raw spec files are developer-oriented and lack user-ready summaries.

## Decision: replacement references

Use existing `/app/*` list pages when they help users reach current functionality; object-specific screens require a real ID. Explain superseded approaches in text without a GitHub link. Do not link to deleted Web routes. Parser and other internal changes explain their effect without an artificial example or site destination.

## Decision: access and interaction

Reuse `requiresAuth` and existing login return URL. Place a link only in authenticated navigation. Semantic `<details>/<summary>` matches the current menu and supplies keyboard disclosure without a new dependency. Records are bundled static data and need no API. Browser bundles remain publicly downloadable; the established SPA guard controls page access.
