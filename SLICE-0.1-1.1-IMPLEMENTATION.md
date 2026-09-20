# Edutech v1.0 — Slice 0.1 / 1.1 Implementation Record

## Status

**Implemented locally; pending Git remote connection and review.**

## Implemented in this slice

This first slice establishes a compatibility-safe Edutech v1.0 identity and the first single-package release foundation.

- Updated the WordPress plugin display name to **Edutech v1.0 — Education Management Platform**.
- Added public Edutech version constants without renaming legacy `WLSM_*` constants.
- Added `WLSM_Brand`, a centralized public brand service with the `edutech_brand` filter.
- Loaded the brand service from the existing plugin bootstrap.
- Preserved the legacy text domain, database identifiers, class names, and compatibility URL for the migration period.
- Added `build-edutech-plugin.sh`, which validates first-party PHP syntax and builds one installable plugin ZIP.
- Added a package manifest containing the Edutech release identity and legacy compatibility version.
- Added a checksum for each generated package.

## Deliberately not completed in this slice

The following remain later slices and must not be represented as complete:

- Frontend login replacement for every role.
- Central authorization and school-scope policy enforcement.
- Frontend configuration center.
- Grading approval and immutable result workflow.
- Question bank and official CBT engine.
- Multi-school, multi-currency, country packs, and curriculum packs.
- Native Android and iOS API implementation.
- Elementor and Gutenberg integrations.
- Backup, import, privacy, observability, and webhook modules.

## Acceptance evidence

Run:

```bash
./build-edutech-plugin.sh
```

The command must produce:

```text
release/edutech-v1.0.0.zip
release/edutech-v1.0.0.zip.sha256
```

The package must install as one WordPress plugin, contain the Edutech entry-point header, preserve the legacy migration identifiers, and pass PHP syntax validation for first-party files.

## Next slice

Slice 0.2 / 0.3 should add the local test harness, compatibility inventory, staging migration checks, and a release checklist that records every roadmap slice as `planned`, `in_progress`, `implemented`, `tested`, `accepted`, or `deferred`.
