# Edutech v1.0 — Dependency and Test Status

## Slice 0.2 / 0.3 status

**Implemented and tested locally; dependency security remediation remains open.**

PHP CLI, Composer, and the required PHP extensions are installed in the development environment. The locked Composer dependencies were installed with `composer install --no-dev --prefer-dist --optimize-autoloader`.

| Dependency | Installed version | Status |
|---|---:|---|
| `firebase/php-jwt` | 5.5.1 | Requires upgrade planning; Composer reports advisories affecting the 5.x line. |
| `guzzlehttp/guzzle` | 6.5.8 | Requires upgrade planning; Composer reports current host, cookie, redirect, and proxy advisories affecting the 6.x line. |
| `kreait/firebase-php` | 5.26.0 | Legacy line; also brings an abandoned `kreait/clock` package. |
| `stripe/stripe-php` | 13.13.0 | Installed from the locked manifest. |
| `twilio/sdk` | 5.42.2 | Installed from the locked manifest. |

The lock metadata was synchronized with the completed Composer manifest. The manifest now has a package name, description, plugin type, and GPL license declaration.

## Validation completed

- Composer manifest validation passes with non-blocking warnings.
- Composer platform requirements pass after installing PHP 8.3 CLI and the required extensions.
- Composer autoload file is present.
- **549 first-party PHP files pass `php -l`.**
- The Edutech v1.0 single-plugin package builds successfully.
- The generated ZIP passes `unzip -t` integrity validation.
- GitHub Actions CI has been added for future pushes and pull requests.

## Security finding

Composer reports **17 security advisories affecting 5 packages** in the current legacy dependency graph. The audit output is retained locally as `composer-audit.txt` and CI uploads an audit artifact. CI does not hide the result, but the audit step is currently non-blocking so the source and test pipeline can continue while compatibility work is performed.

The advisories must be resolved before production release. The safe remediation sequence is:

1. Inventory every call site for JWT, Guzzle, Firebase, Stripe, and Twilio.
2. Add compatibility tests around authentication, HTTP clients, Firebase, payments, and notifications.
3. Upgrade one dependency family at a time.
4. Run the full smoke, integration, upgrade, and payment callback tests after each upgrade.
5. Remove the non-blocking audit exception once the report is clean or formally risk-accepted by the product owner.

Do not perform a blind major-version upgrade on the legacy plugin because the current code may rely on removed APIs or behavior.

## Next slice

Slice 1.1/1.2 should introduce the module manifest, centralized migration version, staging database smoke tests, and the first compatibility adapters for the new Edutech namespace. Dependency remediation should be tracked as a security-gated workstream before official CBT, payments, or public mobile API release.
