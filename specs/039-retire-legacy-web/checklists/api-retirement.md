# API and retirement requirements checklist

**Purpose**: Review the completeness of route, export, and cleanup requirements  
**Created**: 2026-09-26  
**Feature**: [spec.md](../spec.md)

`[x]` means the reviewer found the requirement clear enough to implement. It does not mark implementation complete.

## Route contract

- [x] CHK001 Are the four useful old cup actions mapped to V1 routes and the unused group export retired? [Completeness, FR-001]
- [x] CHK002 Are authentication and missing-resource outcomes specified for the full export? [Clarity, FR-002, FR-004]
- [x] CHK003 Is the root redirect and absence of old mutation routes stated? [Coverage, FR-006]

## Export contract

- [x] CHK004 Is the full download defined, including file type and attachment behavior? [Completeness, FR-003]
- [x] CHK005 Are CSV column, quoting, empty-table, and ranking consistency requirements stated? [Edge case, FR-003]
- [x] CHK006 Is the relation between export calculations and the existing table view specified? [Consistency, FR-008]

## Removal scope

- [x] CHK007 Are browser-only assets distinguished from still-used mail templates? [Scope, FR-007]
- [x] CHK008 Does the service inventory requirement demand active callers for every retained class? [Traceability, FR-010]
- [x] CHK009 Are scoring and protocol cleanup acceptance cases retained after `DistanceService` removal? [Coverage, FR-009]

## Notes

Requirements review complete. `$speckit-implement` reads this checklist and does not alter its markers.
