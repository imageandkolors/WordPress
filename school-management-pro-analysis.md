# Edutech v1.0 — Project Analysis of the Supplied School Management Pro Archive

## Executive assessment

The supplied archive is **School Management Pro 10.7.1**, which is the legacy codebase being evaluated for the **Edutech v1.0** rebrand. It is a large WordPress plugin that implements a multi-school education ERP. It covers school and staff administration, admissions, attendance, exams, certificates, fees, accounting, library, transport, hostel, communication, live classes, support tickets, and student-facing REST APIs. The archive also includes a substantial HTML documentation site and GPL licensing material.

The project is **feature-complete in breadth but high-risk to evolve into Edutech v1.0 without staged hardening**. Its core strengths are domain coverage, WordPress-native integration, a dedicated database layer, localization files, and bundled integrations for payments, SMS, Firebase, and communications. Its main weaknesses are a monolithic architecture, limited evidence of automated quality controls, a 31,837-line REST API class, extensive custom schema migration logic, and authorization that is often declared at the route boundary as only “the user is logged in.” The latter is not by itself proof of a vulnerability because many handlers perform additional identity checks, but it warrants a route-by-route security review before exposing the API to untrusted clients.

The most valuable next step is **not** a wholesale rewrite. It is a staged hardening program: establish repeatable tests and static analysis, centralize authorization and data-scope checks, then extract the largest subsystems behind stable service boundaries.

## What is in the archive

The outer archive contains the GPL license files, `Documentation.zip`, and a nested `school-management-pro.zip`. The nested application contains the WordPress plugin source and bundled Composer dependencies.

| Area | Finding |
|---|---|
| Application type | WordPress plugin, PHP-first |
| Declared version | 10.7.1 |
| Main entry point | `school-management.php` |
| First-party PHP files | 548 |
| First-party JavaScript files | 28 |
| First-party CSS files | 39 |
| First-party PHP size | Approximately 184,772 lines |
| Largest first-party file | `public/api/WLSM_Api.php`, approximately 31,837 lines |
| Database | Custom `wp_`-prefixed tables created and altered by `WLSM_Database` |
| Dependencies | Composer lock file with 35 packages, including Stripe, Twilio, Firebase, Google Cloud, and Guzzle packages |
| Documentation | Static HTML guide with screenshots and feature walkthroughs |
| Automated tests/CI | No PHPUnit suite, CI configuration, or project-level lint configuration was found in the extracted source |

The counts exclude the vendored dependency tree and language catalogs where appropriate. They describe the supplied distribution, not necessarily the complete upstream development repository.

## Architecture and code organization

The plugin uses a conventional WordPress bootstrap. `school-management.php` defines plugin constants, registers admin and public loading, installs the login redirect filter, and registers activation, deactivation, and uninstall hooks. The application is then organized into three broad areas:

1. **`admin/`** contains WordPress admin menus, school-manager screens, staff modules, reports, forms, print views, settings, and save/route handlers.
2. **`public/`** contains public shortcodes, registration and student-facing flows, frontend assets, and the REST API.
3. **`includes/`** contains constants, helpers, model-like database access classes, notification and messaging integrations, and Composer dependencies.

This is a recognizable WordPress plugin structure and should be approachable for developers familiar with the platform. The main architectural concern is that the code is organized primarily by feature files rather than by stable application layers. Presentation templates, request handling, SQL access, validation, and integration calls are frequently close together. That makes a change in a shared domain rule likely to require edits in several unrelated paths.

The API is the clearest concentration point. `public/api/WLSM_Api.php` contains dozens of route registrations and a very large collection of static handler methods. This increases merge conflicts, makes security review difficult, and encourages duplicated input validation and response shaping.

## Functional strengths

The product surface is unusually broad for a WordPress plugin. The documentation and source show support for multi-school administration, school-specific settings, role management, academic records, attendance, examinations, financial workflows, library and transport operations, student portals, and printed documents. The design also includes multiple notification and payment providers, which gives installations flexibility when local providers differ.

The code uses WordPress conventions in several important places. It relies on WordPress hooks, `$wpdb`, `dbDelta`, WordPress users and user metadata, translation functions, nonces in many admin interactions, and common escaping functions such as `esc_html`, `esc_attr`, and `esc_url`. The REST handlers frequently derive the student identity from `get_current_user_id()` and then resolve the corresponding student record, which is a better pattern than trusting a client-supplied student identifier for the main student portal flows.

The supplied documentation is a practical strength. It includes screenshots for many workflows and gives an operator a path through setup, school configuration, admissions, attendance, exams, finance, transport, and student features. That reduces onboarding friction compared with a source-only distribution.

## Key risks and evidence

### 1. Maintainability risk is high

The application contains approximately 184,772 lines of first-party PHP. Several files are especially large: `WLSM_Api.php` is approximately 31,837 lines; `WLSM_Staff_General.php` is approximately 14,223 lines; `WLSM_Staff_Class.php` is approximately 8,437 lines; and `WLSM_Staff_Accountant.php` is approximately 7,409 lines. These files are likely to be difficult to test in isolation and difficult to modify safely.

**Impact:** changes can have broad regressions, code review becomes slower, and security fixes require searching through many duplicated paths.

**Recommendation:** introduce service classes around stable domains such as students, attendance, examinations, finance, and transport. Keep the current templates as a compatibility layer initially. Move validation, authorization, and persistence into services before attempting larger structural changes.

### 2. REST authorization needs a systematic review

The API registers a large number of routes. The route registrations commonly use a `permission_callback` that returns `is_user_logged_in()`. Many student handlers then call `get_current_user_id()` and resolve a student with `WLSM_M_User::user_is_student()` or a related helper. This is a useful second layer for student-owned data, but the route-level gate is broad and the full API includes administrative and parameterized operations as well as read-only student endpoints.

**Impact:** a logged-in user may reach handlers that were intended for a narrower role unless every handler performs an explicit capability, school-scope, and resource-ownership check. Object-level authorization problems are especially important here because the data includes student records, attendance, grades, fees, contact details, and payment-related information.

**Recommendation:** create named permission callbacks for each role and domain. Require both capability and school scope where applicable. For every route containing an ID, verify that the requested object belongs to the current user, permitted school, or permitted staff assignment. Add negative tests for cross-student, cross-school, and cross-role access. Treat this as a high-priority security review item, not as a confirmed exploit based on static inspection alone.

### 3. Database installation and migration logic is operationally sensitive

`admin/inc/WLSM_Database.php` creates a large custom schema and contains many conditional `ALTER TABLE` operations. Activation also performs database engine changes and inserts or updates default data. Uninstall is safer than many plugins because destructive table removal is conditional on `wlsm_delete_on_uninstall`, but the activation path remains a high-impact operation.

**Impact:** activation or upgrade can be slow on large schools, can fail partway through, and can leave a partially migrated database. The schema is managed procedurally rather than through a clearly versioned migration set, which makes upgrade testing harder.

**Recommendation:** add an explicit schema version option and one idempotent migration function per version. Record migration results and failures. Avoid combining a full schema build with a routine plugin activation path. Test upgrades from representative older versions on large datasets, including interrupted migrations and indexes on high-volume tables.

### 4. The project has limited visible automated quality controls

No PHPUnit suite, CI configuration, PHP_CodeSniffer configuration, or project-level JavaScript package manifest was found in the extracted plugin. A PHP syntax check could not be executed in this sandbox because the PHP CLI is not installed; therefore syntax validity is not claimed by this analysis.

**Impact:** regressions in financial calculations, permission logic, migrations, and generated documents may be detected only after deployment or manual testing.

**Recommendation:** start with a minimal test harness rather than attempting full coverage. Add tests for database migrations, student identity resolution, invoice totals, attendance aggregation, role permissions, and REST authorization. Add PHP linting, WordPress coding standards, and a CI job that packages the plugin and validates that required Composer files are present.

### 5. Dependency and packaging hygiene should be tightened

The plugin includes `includes/composer.json` and `includes/composer.lock`, which is good for reproducibility. The lock file contains 35 packages, including Stripe 13.13.0, Twilio 5.42.2, Firebase PHP JWT 5.5.1, Kreait Firebase PHP 5.26.0, Guzzle 6.5.8, and Google Cloud packages. The complete vendor tree is included in the distribution, which is expected for a WordPress plugin but makes package provenance and update tracking important.

**Impact:** old transitive dependencies can accumulate, and a distribution package can drift from the development lock file if release packaging is not automated. The use of Guzzle 6 also places an upper bound on the HTTP client generation and should be reviewed against the supported PHP and WordPress matrix.

**Recommendation:** make the lock file the single release source of truth. Run dependency audits in CI, document the supported PHP/WordPress versions, and generate release archives from a clean build. Keep third-party notices and license metadata synchronized with the bundled libraries.

### 6. Sensitive configuration deserves additional protection review

The plugin supports storing payment, SMTP, SMS, Firebase, Zoom, and other service credentials in school settings. The settings screens correctly escape values for HTML output in the inspected paths, but static inspection does not establish whether all credentials are encrypted at rest, excluded from logs, masked in exports, or protected from lower-privileged school staff.

**Recommendation:** define a credential-handling policy. Mask secrets in settings screens, exports, debug logs, and REST responses. Restrict settings by capability and school scope. Consider WordPress secrets or an external secret store for high-value deployments. Add tests that assert credentials never appear in API payloads or generated reports.

### 7. SQL safety is mixed and needs focused review

Many application queries use `$wpdb->prepare`, and the code commonly uses integer sanitization and WordPress escaping. Raw SQL is also used extensively for schema creation and migrations, where interpolation of plugin-defined table constants is normal. Static searches still identified enough direct database activity to justify a focused review of every query that combines request-derived values with SQL.

**Recommendation:** enforce a rule that all request-derived SQL values pass through `$wpdb->prepare` or structured `$wpdb` methods. Review dynamic `ORDER BY`, `LIMIT`, column, and table fragments separately because placeholders cannot safely represent identifiers. Add tests for malformed IDs, unexpected arrays, and boundary pagination values.

## Data model and operational considerations

The custom table design is appropriate for a feature-rich ERP because it avoids forcing complex academic and finance relationships into WordPress posts and post metadata. The trade-off is that the plugin owns a substantial relational schema while also depending on WordPress users and options. This creates two consistency boundaries: WordPress identity data and plugin domain data.

The main data-integrity risks are migration ordering, partial upgrades, duplicate records in high-volume areas such as attendance and invoices, and unclear deletion semantics when a school, class, or student is removed. The changelog indicates that the project has already needed fixes for duplicate attendance counting and database relationship issues, which reinforces the value of invariant-based tests around these domains.

## Recommended priorities

### First 30 days: reduce immediate risk

1. Build a route inventory from `WLSM_Api.php`, including method, parameters, permission callback, capability, school scope, and resource-ownership rule.
2. Add regression tests for cross-school and cross-student access to every parameterized API endpoint.
3. Add PHP linting, WordPress coding-standard checks, and dependency vulnerability scanning to CI.
4. Document the supported PHP and WordPress versions and verify them against the bundled SDKs.
5. Add backup and rollback guidance for activation and upgrade operations.

### Next 60–90 days: improve change safety

1. Split the REST API into domain controllers while preserving endpoint paths and response formats.
2. Extract shared authorization, validation, pagination, and response helpers.
3. Convert database upgrades into explicit, versioned, idempotent migrations.
4. Add tests for invoices, fees, attendance summaries, exams, promotions, transfers, and generated documents.
5. Introduce a clean packaging workflow that builds the vendor tree from the lock file and verifies licenses.

### Longer term: lower structural cost

1. Separate domain services from WordPress presentation and routing.
2. Define stable interfaces for payment, messaging, video, and notification providers.
3. Add observability for migration failures, payment callbacks, notification failures, and background jobs.
4. Establish data-retention and credential-rotation policies suitable for student and financial records.
5. Replace duplicated feature-specific SQL and permission checks with reusable domain policies.

## Verification limits

This was a static analysis of the supplied archive and its documentation. The archive was unpacked successfully, source structure and key files were inspected, and code-pattern searches were run. A PHP syntax check was attempted but could not run because the sandbox does not have the PHP CLI installed. No WordPress installation, database, configured payment provider, or authenticated API client was available, so runtime behavior, migration execution, endpoint authorization, and third-party callbacks were not tested.

The authorization concerns in this report are therefore **review priorities**, not confirmed vulnerabilities. The report also does not claim that every direct SQL operation is unsafe; the relevant distinction is whether request-derived values reach an unprepared query.

## Conclusion

The project is a substantial and commercially useful WordPress ERP plugin rather than a small application. Its functional foundation is strong, and the supplied documentation is better than average for a distribution package. The project should be treated as a **high-value legacy system that needs systematic hardening**, not as a candidate for an immediate rewrite. A focused security review of the API and admin actions, combined with migration tests and basic CI, would provide the largest reduction in operational risk for the least disruption.

## References

[1]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/school-management.php "Plugin bootstrap and lifecycle hooks"
[2]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/public/api/WLSM_Api.php "REST API route registrations and handlers"
[3]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/admin/inc/WLSM_Database.php "Database schema creation and migration logic"
[4]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/includes/composer.json "Composer dependency requirements"
[5]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/includes/composer.lock "Locked Composer dependency versions"
[6]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/changelog.txt "Project changelog"
[7]: file:///home/ubuntu/work/school-management-analysis/docs/Documentation/index.html "Supplied product documentation"
