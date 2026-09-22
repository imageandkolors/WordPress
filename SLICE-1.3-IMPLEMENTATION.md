# Edutech v1.0 — Slice 1.3 Implementation Record

## Status

**Implemented and locally tested; staging acceptance is still required before this slice is marked accepted.**

## Delivered

Edutech now has a centralized feature-flag service for controlled release of the frontend portal, branding, CBT, mobile API, country packs, Elementor integration, Gutenberg integration, and frontend module controls.

Feature flags are stored under the existing `edutech_feature_flags` option. The service remains backward-compatible with the flat option shape created by Slice 1.1 and normalizes it in memory into a scope-keyed structure. Platform defaults are preserved, and new unfinished capabilities default to disabled.

Flags can be evaluated at platform scope or for a specific school. A school-specific value overrides the platform value; otherwise the platform value is inherited. Unknown features return disabled and cannot be enabled. Invalid school scopes return a `WP_Error` rather than silently changing platform behavior.

The service exposes a release filter so a controlled pilot, staging rule, or future frontend designer workflow can apply temporary release policy without bypassing the stored configuration. State changes emit an audit-ready action hook and do not delete module or school data.

## Feature keys

| Key | Default | Intended use |
|---|---:|---|
| `portal` | Off | Frontend-first portal rollout. |
| `branding` | On | Edutech public identity. |
| `cbt` | Off | Official and practice CBT engines. |
| `mobile_api` | Off | Native Android and iOS API rollout. |
| `country_packs` | Off | Country, curriculum, and examination packs. |
| `elementor` | Off | Elementor integration. |
| `gutenberg` | Off | Gutenberg integration. |
| `frontend_modules` | Off | Frontend designer module controls. |

## Verification

- Feature-flag compatibility test passes.
- Edutech smoke tests pass.
- **553 first-party PHP files pass syntax validation.**
- Static security scan passes after updating the guarded-file baseline.
- JWT compatibility test passes.
- Single-plugin ZIP builds and passes integrity checks.
- Package manifest declares `release_controls: feature-flags-v1`.

## Staging acceptance still required

Staging must verify platform enablement, school-level pilot enablement, inherited platform values, disabling a pilot, invalid school IDs, unknown keys, cache behavior, audit-hook capture, and fallback behavior when a capability is disabled. The frontend designer screen for changing flags is intentionally deferred to the frontend application-shell slice so no operational user is sent to WordPress admin.

## Rollback

Disable the affected feature at platform or school scope, return the affected module to its supported fallback, and preserve all records. If the release-control option is corrupted, restore the WordPress options/database backup and disable the feature service hooks; no business tables are deleted by this slice.
