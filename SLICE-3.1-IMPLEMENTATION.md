# Edutech v1.0 — Slice 3.1 Implementation Record

## Status

**Implemented and locally tested; staging security acceptance is still required before this slice is marked accepted.**

## Delivered

The existing frontend login flow now uses a centralized Edutech authentication service around WordPress’s native authentication APIs. The plugin does not replace `wp_login_form()`, WordPress cookies, password-reset endpoints, or core user records.

Frontend login arguments are branded for Edutech and use stable Edutech form IDs. Login redirects are validated as same-site URLs with a safe home-page fallback, including the legacy staff-dashboard redirect path. This closes the open-redirect risk without removing the existing staff compatibility behavior.

Failed login attempts are throttled with a transient counter keyed by a normalized username and remote address. Five failed attempts within a fifteen-minute window receive a generic throttling error. Successful login clears the counter. The failure response does not reveal whether a username exists.

The account view now presents the login form inside a scoped Edutech authentication card. A non-sensitive session-expired notice can be shown with `edutech_session=expired`. Suspension messaging remains compatible with the existing WLSM flow. Lost-password links continue to use WordPress’s native password-recovery endpoint and retain a safe same-site redirect.

## Security controls

| Control | Implementation |
|---|---|
| Same-site redirect validation | `Edutech_Auth::safe_redirect()` with home fallback. |
| Login throttling | Five attempts per username/IP key within 900 seconds. |
| Generic throttle message | Does not disclose account existence. |
| Counter reset | Successful `wp_login` clears the transient. |
| Native authentication | WordPress `authenticate`, `wp_login_failed`, and `wp_login` hooks. |
| Session-expiry notice | Scoped frontend status notice without sensitive details. |
| Password recovery | Existing WordPress lost-password flow retained. |
| Frontend branding | Edutech form IDs, labels, action text, and auth-card metadata. |

## Compatibility policy

The WLSM login helper remains in place as a compatibility adapter. Existing role resolution, staff dashboard behavior, WordPress users, cookies, password hashes, lost-password flow, and legacy URLs are preserved. This slice does not create duplicate users or migrate authentication data.

## Verification

- Authentication compatibility test passes.
- Edutech smoke tests pass.
- **556 first-party PHP files pass syntax validation.**
- Static security scan passes after updating the guarded-file baseline.
- REST permission coverage remains 282 routes: 281 centralized protected routes and one intentional public settings route.
- JWT compatibility test passes.
- Feature-flag and portal-wrapper tests pass.
- Single-plugin ZIP builds and passes integrity checks.
- New authentication service and frontend styles are included in the installable package.
- Git diff whitespace validation passes.

## Staging acceptance still required

Test successful login for students, parents, teachers, staff, school administrators, examination officers, and the designer exception. Test invalid credentials, five-attempt throttling, successful counter reset, different IP behavior, safe internal redirects, rejected external redirects, suspended accounts, logout, lost-password, reset-password, expired-session messaging, multilingual labels, and keyboard accessibility. Verify that rate limiting works across multiple web servers if object caching is enabled.

The production deployment must use a persistent object cache or a shared rate-limit store if the site runs on multiple application nodes. Transient-based throttling is suitable for the current single-site foundation but requires infrastructure validation before high-volume production rollout.

## Rollback

Deploy the previous commit to restore the prior frontend form identifiers and helper behavior. Existing WordPress credentials and password-reset records are unaffected. If throttling must be disabled temporarily, remove the Edutech authentication hooks only after documenting the incident and compensating controls.
