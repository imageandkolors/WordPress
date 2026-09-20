# Edutech v1.0 — Slice 0.2 / 0.3 Implementation Record

## Status

**Implemented and tested locally; pushed with the next commit after verification.**

## Implemented

- Installed PHP 8.3 CLI and required extensions: cURL, XML, mbstring, ZIP, MySQL, and Intl.
- Installed Composer 2.x.
- Validated and completed the Composer package metadata.
- Synchronized the Composer lock metadata.
- Installed the locked runtime dependencies with optimized autoloading.
- Added a repeatable `tests/smoke-test.sh` harness.
- Added GitHub Actions CI for Composer validation, dependency installation, platform checks, PHP syntax checks, package build, and dependency auditing.
- Added dependency status documentation and a security remediation record.
- Added the Edutech single-package ZIP build to the smoke test.

## Verification results

- Composer platform requirements: passed.
- Composer autoload: present.
- First-party PHP syntax: **549 files passed; 0 failures**.
- Single-plugin ZIP build: passed.
- ZIP integrity test: passed.
- GitHub Actions workflow: added and ready for the next push.

## Open security gap

Composer reports 17 advisories affecting 5 packages in the legacy dependency graph. The full list is recorded in `DEPENDENCY-STATUS.md`. This is not being hidden or treated as resolved.

The dependency upgrade must be performed as a separate compatibility workstream with tests around authentication, HTTP calls, Firebase, payments, notifications, and any code using JWT. Official CBT, payments, and public mobile API release should remain security-gated until the report is clean or formally risk-accepted.

## Next slice

Slice 1.1/1.2 should add the module manifest, centralized database migration version, staging migration checks, and compatibility adapters for the Edutech namespace while keeping the legacy WLSM identifiers operational.
