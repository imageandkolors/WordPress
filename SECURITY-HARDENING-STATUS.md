# Edutech v1.0 — Security Hardening Status

## Scope

This pass addresses the security gaps visible in the supplied legacy plugin before the next feature slice. It covers dependency exposure, secret handling, REST permissions, direct file access, dangerous execution patterns, and CI enforcement. It is a hardening increment, not a substitute for a full penetration test on a staging WordPress installation.

## Fixes implemented

The REST API contained 281 routes. Two hundred and eighty-one routes used a callback that only checked whether a WordPress user was logged in. Those callbacks now use one centralized `WLSM_Api::permission_logged_in()` policy. The policy rejects anonymous requests, rejects authenticated accounts that are not associated with a student, parent, staff record, or privileged designer account, and returns proper 401/403 REST errors. Object-level and school-scope authorization remain callback-level work and are explicitly not considered complete by this change.

The public global-settings endpoint remains intentionally readable because it returns only date format and currency code. It must be reviewed again when multi-school and multi-currency context are introduced.

The non-runtime vendored Stripe README that contained a provider test credential was removed from the working tree and excluded from Git. The release builder therefore cannot redistribute that documentation credential. No secret or private-key pattern was found in the non-vendored source scan.

Three first-party rendering partials that lacked WordPress direct-access guards now include `ABSPATH` checks. A Zoom authorization link now uses `_blank` with `noopener noreferrer`.

`firebase/php-jwt` was upgraded from 5.5.1 to 6.11.1. The dependency graph resolves, platform requirements pass, and the Zoom JWT call site must remain covered by integration tests before production use.

A static security scan now runs in CI. It checks for credential literals, direct-access guard coverage, REST permission coverage, dangerous execution functions, and the removed vendored credential file.

## Current test evidence

The local smoke test validates Composer, platform requirements, autoloading, Edutech metadata, the complete first-party PHP tree, and the single-plugin ZIP. The static security scan validates the new permission and secret controls. The CI workflow runs both checks on pushes and pull requests.

## Remaining security gates

The Composer audit still reports vulnerabilities in the legacy Guzzle/Firebase dependency chain. Guzzle 6.x cannot be upgraded independently because the current Firebase Admin SDK stack pins the 1.x Guzzle promises and PSR-7 lines. A coordinated Firebase/Guzzle migration is required, followed by compatibility tests for Firebase messaging, Zoom, HTTP redirects, and every payment or notification integration.

The current REST policy is an identity gate, not a complete authorization policy. Every route that accepts an object ID, student ID, invoice ID, payment ID, class ID, staff ID, or school ID still requires a route-by-route school-scope and ownership review. This is particularly important for parent child-switching, staff operations, invoices, payments, examination results, certificates, and future mobile clients.

The plugin still contains many unauthenticated AJAX registrations. Some are intentionally public admissions, inquiries, catalog lookups, and payment callbacks; others need nonce, signature, rate-limit, origin, and ownership review. Payment-provider callbacks must use provider signature verification rather than a browser nonce. This work must be tested per gateway and must not be changed through a broad search-and-replace.

File uploads and downloads require a dedicated review for MIME allowlists, file-size limits, randomized names, private storage, authorization before download, and executable-file prevention. Sensitive certificates, transcripts, invoices, medical records, and student documents must not be exposed through permanent public URLs.

The plugin needs a full authentication hardening slice for frontend login, password reset, brute-force throttling, session revocation, two-factor authentication, device management, and secure mobile tokens. The current WordPress login helper is not sufficient for the final Edutech mobile architecture.

## Release rule

Edutech v1.0 must not be marketed as security-complete until the remaining dependency, object-authorization, payment-callback, upload/download, and authentication gates have passed staging tests and an external or independent security review. Official CBT, payment processing, and public mobile API release should remain blocked by unresolved critical or high-severity findings.
