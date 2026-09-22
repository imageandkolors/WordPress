# Edutech v1.0 — Slice 2.3 Implementation Record

## Status

**Implemented and locally tested; visual staging acceptance is still required before this slice is marked accepted.**

## Delivered

The account shortcode now wraps the existing legacy account output in a theme-independent Edutech portal frame. The wrapper accepts a validated `mode` attribute with three supported values:

| Mode | Behavior |
|---|---|
| `embedded` | Constrained content suitable for a normal page template. This is the default and the safest compatibility mode. |
| `full-width` | Uses the available content width and applies responsive horizontal padding. |
| `standalone` | Provides a larger app-like surface with minimum viewport height and a subtle portal background. |

Unknown modes safely fall back to `embedded`. The wrapper escapes the accessible label and emits a `data-edutech-mode` attribute for future layout controllers. Legacy account markup remains inside the wrapper and is not rewritten.

System-page metadata is now centralized in `Edutech_Portal_Pages`. Definitions exist for login, portal, practice CBT, and official CBT pages. `ensure()` and `ensure_all()` can create missing pages or reuse an existing page with the same slug. Existing custom pages are not overwritten, and no page is deleted during regeneration. The service stores page IDs in a dedicated option and exposes a filter for future modules to register additional system pages.

The portal wrapper and page service are loaded from the Edutech bootstrap, while the existing shortcode and legacy WLSM identifiers remain intact.

## Example usage

```text
[school_management_account mode="embedded"]
[school_management_account mode="full-width" label="School Portal"]
[school_management_account mode="standalone" label="Student Portal"]
```

## Verification

- Portal-wrapper compatibility test passes.
- Edutech smoke tests pass.
- **555 first-party PHP files pass syntax validation.**
- Static security scan passes after updating the guarded-file baseline.
- REST permission coverage remains 282 routes: 281 centralized protected routes and one intentional public settings route.
- Single-plugin ZIP builds and passes integrity checks.
- New portal services and wrapper CSS are included in the installable package.
- Git diff whitespace validation passes.

## Visual staging acceptance still required

Test all three modes in a classic theme, block theme, and Elementor theme. Confirm that embedded mode does not break theme spacing, full-width mode does not escape the intended content container unexpectedly, and standalone mode is usable without manual theme-file edits. Test missing-page regeneration, an existing custom page with the same slug, deleted system-page metadata, multilingual titles, right-to-left layout, keyboard navigation, and mobile widths.

## Rollback

Deploy the previous commit or remove the wrapper call while leaving the legacy account route active. Remove only the Edutech page option if page metadata must be reset; do not delete existing pages or legacy WLSM records as part of rollback.
