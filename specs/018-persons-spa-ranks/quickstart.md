# Quickstart: SPA Person Rank History

## Backend

vendor/bin/phpunit tests/Feature/Api/V1/PersonPrompt/PersonPromptApiTest.php tests/Feature/Api/V1/PersonRankHistory tests/Feature/Rank/LegacyRankPageRemovalTest.php

Verify that View Person remains compact, `/persons/{personId}/rank-histories` returns all raw history fields, `/events?withCompetition=1&ids[]=...` returns referenced labels, and mutation endpoints preserve activation behavior.

## SPA

npm run ci

Verify the nested ranks tab, reverse-chronological timeline, localized change types, anonymous read-only state and authenticated activation actions.

## Quality gates

composer cs -- --sequential
composer stan
composer rector -- --dry-run
git diff --check
