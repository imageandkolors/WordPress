# Edutech v1.0 — Phased Slice-by-Slice Implementation Roadmap

## 1. Purpose and delivery rule

This roadmap converts the identified gaps into an executable sequence of small releases, called **slices**. Each slice should produce a working, testable increment of the product. A slice is not complete when code has been written; it is complete when its acceptance criteria pass on a staging installation and the migration path is documented.

The target product is **Edutech v1.0**, a plug-and-play, frontend-first, modular education management platform for primary, secondary, tertiary, and vocational institutions. It must support multiple schools and campuses, multiple currencies, configurable assessment and grading systems, curriculum and syllabus packs, school and practice computer-based testing, secure result approval, complete rebranding, and compatibility with WordPress, Elementor, and Gutenberg.

The **designer/platform owner** remains the only routine operational user who may use WordPress admin. Teachers, students, parents, school administrators, examination officers, accountants, and other staff must use the frontend portal.

The most important implementation rule is:

> **Build the foundation once, expose it through shared services, and migrate each feature slice through the same frontend, authorization, audit, and upgrade-safe architecture.**

Do not copy WordPress admin pages into frontend templates. Do not create separate permission logic for each module. Do not implement CBT timing or result approval only in JavaScript. Do not rename legacy database and API identifiers until compatibility adapters exist.

## 2. Delivery model

Each slice follows the same lifecycle:

1. Define the user outcome and data boundary.
2. Confirm dependencies and migration requirements.
3. Implement the shared service and authorization rules.
4. Implement the frontend workflow and responsive UI.
5. Keep the legacy workflow as a compatibility adapter while migration is active.
6. Add automated tests, security tests, and upgrade tests.
7. Run the staging acceptance checklist.
8. Enable the slice through a feature flag.
9. Record the release and rollback procedure.

A slice should normally be released only when it has a working empty state, loading state, validation state, error state, success state, mobile layout, keyboard-accessible controls, audit behavior, and permission tests.

The final customer release is **one installable Edutech v1.0 WordPress plugin**. Optional capabilities such as CBT, curriculum packs, Elementor, Gutenberg, payment providers, and country packs may be delivered as modules inside the repository, but the customer-facing installation must be one tested plugin package. A school must not manually assemble PHP folders or edit configuration files to install a supported module.

Every slice must have a tracked status: `planned`, `in_progress`, `implemented`, `tested`, `accepted`, or `deferred`. A slice cannot be marked complete merely because its code exists; its migration, authorization, UI, tests, documentation, feature flag, and rollback behavior must be recorded.

## 3. Product architecture to preserve throughout the roadmap

The final product should be divided into a **core platform**, **functional modules**, **advanced engines**, **country and examination packs**, and **external integrations**.

### Core platform

The core contains authentication, frontend routing, users, roles, permissions, schools, campuses, academic sessions, student and parent identity, currency and grading foundations, audit logs, notifications, files, the API and service layer, module management, migrations, health checks, and upgrade compatibility. These components must not be optional because every operational module depends on them.

### Functional modules

Functional modules include admissions, student management, parent portal, teacher portal, attendance, timetable, homework, examinations, fees, payments, accounting, library, transport, hostel, certificates, notices, events, staff leave, tickets, reports, and communications.

### Advanced engines

Advanced engines include the official CBT engine, practice CBT, question bank, curriculum and syllabus engine, advanced grading and GPA, transcripts, graduation, moderation, advanced analytics, and high-stakes examination controls.

### Packs and integrations

Country packs provide configurable education structures, grading presets, curriculum data, examination-body structures, and templates. Payment gateways, SMS providers, email providers, Firebase, video services, and authorized external examination APIs remain integrations with independent health checks.

Every module must declare its dependencies, register its own capabilities, add its frontend navigation, run its migrations, register its API routes, and expose a health check. Deactivating a module must hide its navigation without deleting its data.

## 4. Phase 0 — project governance and safety baseline

### Slice 0.1 — Product scope, decisions, and ownership

Create the product decision record. Confirm **Edutech v1.0** as the public product name, the domain, ownership, license obligations, supported countries for the first release, supported institution types, pricing boundaries, and the designer/platform-owner exception.

The output is a signed scope baseline. It must distinguish between platform capabilities, configuration templates, official curriculum content, and external integrations. The product should not claim to contain every country’s official curriculum or examination content unless that content has been sourced, reviewed, licensed, and versioned.

**Acceptance criteria:** the scope document identifies what is in the core, what is an add-on, what is a country pack, and what is an integration. License and trademark review has been recorded.

### Slice 0.2 — Staging, backup, and rollback discipline

Create a repeatable staging environment and test database snapshots. Define backup procedures before plugin activation and before migrations. Create a rollback playbook for failed migrations, disabled modules, broken frontend routes, and incompatible editor integrations.

**Acceptance criteria:** a staging copy can be restored from backup; a failed migration can be detected and retried; the designer can disable a new module without deleting its records.

### Slice 0.3 — Baseline inventory and test harness

Inventory existing database tables, options, user metadata, shortcodes, AJAX actions, REST routes, scheduled hooks, page templates, admin menus, assets, translations, and external integrations. Build a test harness for authentication, school scope, role permissions, database migrations, REST requests, shortcodes, and frontend rendering.

**Acceptance criteria:** the inventory is versioned; critical existing workflows have regression tests or documented manual test scripts; the project can report PHP syntax, coding, database, and migration failures before release.

## 5. Phase 1 — compatibility foundation and module system

### Slice 1.1 — Versioned plugin bootstrap

Refactor the bootstrap into a safe loader that checks PHP, WordPress, required extensions, database capabilities, and optional integrations. Introduce a plugin version, database version, module registry version, and migration runner.

Migrations must be idempotent. They must not assume activation is the only upgrade path. They should record progress and fail safely.

**Acceptance criteria:** fresh install, upgrade from the current archive, repeated activation, and interrupted migration all complete without duplicate tables or lost records.

### Slice 1.2 — Module registry and dependency resolver

Implement the module registry with module key, version, status, dependencies, migration version, configuration status, license status if used, and enabled schools. Add activation, deactivation, health-check, capability-registration, route-registration, and navigation-registration hooks.

**Acceptance criteria:** a module can be activated and deactivated from the frontend designer area; dependencies are detected; a required dependency cannot be disabled while a dependent module remains active; module data remains intact after deactivation.

### Slice 1.3 — Feature flags and release controls

Add feature flags for the Edutech v1.0 portal, Edutech branding, each migrated domain, CBT, country packs, Gutenberg, and Elementor integrations. Flags must be scoped to the platform or individual school where appropriate.

**Acceptance criteria:** the designer can enable a slice for staging or one pilot school; disabling the flag returns users to the supported fallback without deleting data.

## 6. Phase 2 — rebranding and design-system foundation

### Slice 2.1 — Brand service and visible identity

Create a centralized brand service for product name, short name, logos, favicon, colors, support URL, documentation URL, email sender name, certificate branding, and portal title. Replace visible original-vendor branding in the frontend, login, emails, invoices, reports, certificates, documentation, and module screens.

Preserve legacy internal identifiers such as `WLSM_*`, `wlsm_*`, existing options, database names, AJAX actions, REST routes, shortcodes, and translation domain during the first compatibility release.

**Acceptance criteria:** the new brand is consistent in all user-facing surfaces; legacy records and integrations continue working; original required license notices remain intact.

### Slice 2.2 — Frontend design system

Create scoped design tokens and reusable components for the portal shell, navigation, cards, tables, forms, dialogs, alerts, badges, tabs, pagination, file upload, charts, print layouts, loading states, empty states, and error states.

Use a stable portal root so that the plugin does not globally override themes, Elementor layouts, or Gutenberg editor styles. Support responsive desktop, tablet, and mobile layouts, keyboard navigation, accessible focus states, readable contrast, and localization.

**Acceptance criteria:** a component catalogue exists; components render consistently in a classic theme, block theme, and Elementor theme; the portal remains usable on mobile and with keyboard navigation.

### Slice 2.3 — Theme-independent portal wrapper

Implement embedded, full-width, and standalone portal display modes. The active theme should control the outer header and footer when configured. The plugin should not require manual theme-file edits.

**Acceptance criteria:** the portal works without Elementor, inside a classic theme, inside a block theme, and in standalone mode. Missing or deleted system pages can be regenerated without deleting custom pages.

## 7. Phase 3 — frontend identity, roles, and authorization

### Slice 3.1 — Unified frontend authentication

Create branded login, logout, lost-password, reset-password, password-change, session-expiry, suspension, and invalid-login flows. Use WordPress authentication APIs and secure cookies. Add rate limiting, safe redirects, and optional multi-factor authentication for staff and administrators.

**Acceptance criteria:** students, parents, teachers, staff, school administrators, and examination officers can authenticate through one frontend entry point; the designer can still use WordPress admin; password recovery works without exposing account information.

### Slice 3.2 — Role and identity normalization

Preserve compatibility with existing student, parent, staff, employee, administrator, and custom-role records while introducing explicit portal identities and role capabilities. Teachers should be represented as staff with teaching assignments and teacher-specific capabilities rather than being forced into a generic employee screen.

**Acceptance criteria:** each operational user resolves to a portal role, school scope, session scope, and capability set; users with multiple assignments receive the correct context; existing accounts are not duplicated.

### Slice 3.3 — Central policy and authorization service

Implement one authorization service for route access, API access, object access, school scope, session scope, class assignment, subject assignment, parent-child ownership, and workflow-state transitions.

Permissions should include separate abilities such as `enter_scores`, `submit_scores`, `review_scores`, `approve_exam_results`, `finalize_results`, `publish_results`, `manage_settings`, `manage_cbt`, and `view_audit_log`.

**Acceptance criteria:** every frontend route, REST endpoint, legacy adapter, download, export, print view, and scheduled mutation uses the same authorization policy. Hiding a button is never the only access control.

### Slice 3.4 — WordPress admin restriction

Block operational users from ordinary WordPress admin pages, direct admin URLs, and legacy admin actions. Preserve safe exceptions for logout, password reset, required media flows, cron, webhooks, and emergency recovery. The designer remains the platform-maintenance exception.

**Acceptance criteria:** an operational user cannot bypass the portal by entering a direct `wp-admin`, `admin-post.php`, or unauthorized AJAX URL; scheduled jobs and webhooks continue working; emergency recovery is audited.

### Slice 3.5 — Context switching

Implement secure school, campus, academic-session, term, semester, and parent-child context services. Every request must validate the selected context server-side. Context changes must invalidate unsafe caches and create an audit event.

**Acceptance criteria:** users assigned to multiple schools can switch safely; parents can switch linked children; changing an ID in the browser cannot expose another school, session, or child.

## 8. Phase 4 — plug-and-play setup and frontend application shell

### Slice 4.1 — Installation health check

On activation, check environment requirements, create or upgrade plugin tables, detect Elementor and Gutenberg, detect the active theme, register default roles and capabilities, create system pages, and report actionable errors.

**Acceptance criteria:** a fresh installation reaches a usable setup screen without editing PHP, database files, or theme files.

### Slice 4.2 — Setup wizard

Create a guided setup wizard for country, institution type, education level, academic calendar, currency, school identity, modules, default grading scheme, branding, users, classes, subjects, integrations, and launch checks.

Provide templates for Nigerian primary, Nigerian secondary, Nigerian tertiary, international primary, international secondary, university or college, and vocational institutions. Templates must be editable configuration, not hard-coded rules.

**Acceptance criteria:** a designer can install a new school from the frontend wizard; the wizard can resume after interruption; incomplete requirements are shown as actionable tasks.

### Slice 4.3 — Automatic system pages

Create and assign portal login, dashboard, student, parent, teacher, school administration, profile, password reset, notifications, exam results, CBT practice, official CBT, fees, and support pages. Provide regeneration for missing system pages.

**Acceptance criteria:** no ordinary user needs to create shortcodes manually; existing custom pages are not overwritten; page assignments are visible in settings.

### Slice 4.4 — Frontend navigation and portal shell

Build a role-aware dashboard shell with sidebar and mobile navigation. Navigation must depend on active modules, permissions, school context, education level, and user role. It must show loading, empty, error, and notification states.

**Acceptance criteria:** each role sees only its permitted features; inactive modules disappear from navigation; direct route access remains protected.

## 9. Phase 5 — multi-school, institution, session, and academic structure

### Slice 5.1 — School and campus model

Harden the school model for multiple schools, campuses, branches, faculties, departments, and locations. Add school-level branding, timezone, locale, currency, payment settings, grading defaults, curriculum settings, and integration settings.

**Acceptance criteria:** a network administrator can manage multiple schools; school data is isolated; reports and settings cannot cross school boundaries without explicit permission.

### Slice 5.2 — Institution types and academic units

Support primary, secondary, tertiary, combined, vocational, and custom institution types. Add configurable levels, classes, sections, departments, programmes, courses or subjects, credit units, terms, semesters, and quarters.

**Acceptance criteria:** the same installation can model primary classes, secondary subject combinations, and tertiary departments and courses without hard-coded assumptions.

### Slice 5.3 — Academic sessions and periods

Implement sessions, terms, semesters, examination windows, session status, promotion boundaries, and historical access. Freeze the session and period context into official results and financial records.

**Acceptance criteria:** historical results remain tied to the correct session and grading configuration; switching the current session does not change old records.

## 10. Phase 6 — currency, finance foundation, and integrations

### Slice 6.1 — Multi-currency foundation

Support currency code, symbol, decimal precision, formatting, school currency, invoice currency, base reporting currency, exchange-rate history, rounding, discounts, tax rules, and historical exchange-rate snapshots.

Every financial record should store amount, currency, exchange rate used, base amount, and base currency where conversion applies.

**Acceptance criteria:** changing today’s exchange rate does not change old invoices; reports can show transaction and base currencies; each school can use its configured currency.

### Slice 6.2 — Fees and invoice frontend

Move fee structures, invoices, discounts, concessions, receipts, payment status, and student financial history to the frontend. Enforce student, parent, school, and staff access boundaries.

**Acceptance criteria:** parents and students see only permitted records; accountants and school administrators can manage their school’s fees; invoices and receipts use the correct currency and branding.

### Slice 6.3 — Payment and provider integration layer

Create provider adapters for payment gateways rather than embedding provider-specific code in finance services. Add credential masking, test connection, webhook verification, transaction reconciliation, idempotency, and provider health status.

**Acceptance criteria:** a provider can be enabled or disabled without changing finance logic; duplicate callbacks do not create duplicate payments; credentials never appear in API responses or logs.

### Slice 6.4 — Finance and accounting module

Add income, expenses, categories, budgets, reconciliation, refunds, reporting, and audit workflows. Keep accounting permissions separate from general staff permissions.

**Acceptance criteria:** finance actions are school-scoped, auditable, currency-aware, and accessible through frontend workflows.

## 11. Phase 7 — secure assessment, grading, and score approval

### Slice 7.1 — Assessment scheme engine

Create configurable assessment schemes at global, country, school, session, education-level, class, programme, subject, period, and examination levels. Support components such as CA1, CA2, examination, assignment, project, practical, oral, attendance, and custom components.

Validate that active component weights total 100 percent unless the institution explicitly chooses a different calculation model. Store raw maximum, weight, status, and component order.

**Acceptance criteria:** a school can configure `CA1 20% + CA2 20% + Examination 60%`; another school can use a different scheme; teachers see the configured components without creating them manually.

### Slice 7.2 — Teacher score entry

Create frontend score entry for class, subject, session, term or semester, and assessment component. Allow manual entry, safe bulk import, draft saving, absent and exempted statuses, validation, and submission.

The server must recalculate weighted totals from raw scores. The browser must not be trusted for final scores, grades, school IDs, student IDs, or approval status.

**Acceptance criteria:** teachers can enter only assigned subjects and classes; scores above the configured maximum are rejected; incomplete batches are visible; calculated totals cannot be manually overridden.

### Slice 7.3 — Approval workflow

Implement configurable approval stages such as teacher submitted, supervisor reviewed, examination officer reviewed, administrator approved, finalized, and published. The person who entered the score must not approve the same result where separation of duties is enabled.

**Acceptance criteria:** submitted scores become read-only for lower roles; reviewers can approve or return with a reason; only authorized administrators can finalize and publish.

### Slice 7.4 — Immutable result snapshots and amendment workflow

After finalization, store the assessment scheme version, grading-scale version, raw scores, weighted scores, grade, pass status, calculation version, approvers, and timestamps. Lock the result against normal updates and deletion.

Corrections must use an amendment request with a reason, evidence, approval, new version, and retained superseded record. Add tamper-evident hashes and append-only audit events for finalized results.

**Acceptance criteria:** approved results cannot be edited through frontend forms, REST, AJAX, or ordinary admin pages; amendments preserve the old result and show a complete change history.

### Slice 7.5 — Grading and academic record integration

Connect the assessment engine to configurable grading scales, grade points, GPA, pass and fail rules, promotion rules, ranking rules, report cards, transcripts, certificates, and official result publication.

**Acceptance criteria:** old results do not change when a school changes its current grading scale; published results are shown only to permitted students and linked parents; the record shows the exact scheme used.

## 12. Phase 8 — examination engine and curriculum foundation

### Slice 8.1 — Examination management migration

Move exams, exam groups, papers, admit cards, timetables, manual marks, result publication, and report generation from admin workflows to frontend services and screens. Keep existing database structures as compatibility records while the service layer becomes authoritative.

**Acceptance criteria:** examination officers can create and publish examinations from the frontend; students and parents can view permitted timetables and published results; school scope is enforced.

### Slice 8.2 — Curriculum and syllabus engine

Create country, education authority, examination body, curriculum framework, level, class, subject, topic, subtopic, competency, learning objective, syllabus version, effective date, and institution override structures.

Treat WAEC, NECO, BECE, JAMB-related structures, and future examination bodies as configurable packs. The platform should support recording and managing examination data, but direct external integration requires an authorized API.

**Acceptance criteria:** a school can select or customize a curriculum pack; curriculum versions remain attached to relevant assessments and CBT questions; updating a syllabus does not silently rewrite historical results.

### Slice 8.3 — Country and institution packs

Deliver the first approved packs, starting with Nigeria where the scope is confirmed. Include Nigerian education levels, grading presets, subject structures, term patterns, report templates, and examination-body configurations. Add other countries only after their content and licensing have been reviewed.

**Acceptance criteria:** a new school can start from a country template and edit it; the core engine remains country-neutral; packs can be activated without changing core code.

## 13. Phase 9 — question bank and practice CBT

### Slice 9.1 — Question bank foundation

Create question banks, logical questions, immutable question versions, options, answer keys, explanations, media, translations, tags, difficulty, topic, curriculum outcome, examination body, language, approval status, and ownership scope.

Objective question types should be implemented first. Subjective questions should be stored for manual marking rather than treated as automatically gradable.

**Acceptance criteria:** questions can be created, revised, reviewed, approved, retired, searched, filtered, and versioned; the correct answer is never exposed to unauthorized clients.

### Slice 9.2 — Practice CBT

Create practice exams with random selection, option randomization, topic and difficulty filters, timed or untimed attempts, immediate or delayed feedback, explanations, retakes, and mastery analytics.

Practice records must be clearly separated from official examination results.

**Acceptance criteria:** students can practice from the frontend; practice attempts are stored against the student record; practice scores never enter official results automatically.

### Slice 9.3 — Practice analytics and teacher assignment

Allow authorized teachers to assign practice exams, view class analytics, identify revision needs, and review permitted attempts. Parents may see practice summaries only if the school enables it.

**Acceptance criteria:** analytics respect class, subject, school, and parent-child boundaries; practice recommendations do not expose another student’s data.

## 14. Phase 10 — official CBT examination engine

### Slice 10.1 — Official CBT configuration and lifecycle

Create official CBT exam configuration for school, campus, session, level, class, subject, blueprint, question count, marks, negative marking, timers, schedule, attempts, navigation, result publication, and candidate eligibility.

Use lifecycle states from draft through scheduled, open, in progress, submitted, marked, moderated, published, and archived. Freeze exam configuration after the first official attempt begins, except through an audited cancellation or correction workflow.

**Acceptance criteria:** an authorized school user can configure an official exam; an exam cannot open with incomplete grading, eligibility, or question-pool configuration.

### Slice 10.2 — Question pool capacity and uniqueness validator

Before an official exam opens, validate that each subject has enough eligible question versions for the selected uniqueness policy. Support no identical full paper, limited overlap, disjoint allocation, and wave allocation policies.

Each candidate should receive a cryptographically seeded, server-generated question set with randomized order and option order. Store the exact generated snapshot so the attempt is reproducible.

**Acceptance criteria:** the system blocks an exam when the pool is too small; no two candidates receive an identical full paper when the configured policy can support uniqueness; question selection cannot be regenerated by refreshing the browser.

### Slice 10.3 — Official access code and candidate eligibility

Create a secure exam code or candidate token workflow. The student must be authenticated, eligible, and within the schedule. Store code hashes, expiry, rate limits, entry events, and optional invigilator approval.

A shared code should not be treated as complete identity proof for high-stakes exams. Add candidate-specific PINs, seat tokens, or invigilator approval where required.

**Acceptance criteria:** an ineligible student cannot start; expired or abused codes are rejected; successful and failed entry attempts are audited.

### Slice 10.4 — Server-authoritative timing and answer saving

Store server start time, effective deadline, subject start and deadline, question start and deadline, last heartbeat, submission time, and submission reason. The browser may display the timer but cannot control it.

Support automatic answer saving, reconnect, bounded network grace, subject deadlines, per-question deadlines, total deadlines, and idempotent auto-submit.

**Acceptance criteria:** refreshing the page or changing the browser clock cannot reset time; answers acknowledged before a deadline are retained; an expired attempt submits once and cannot be extended through repeated requests.

### Slice 10.5 — Official marking and result finalization

Automatically mark objective answers, route subjective items to authorized markers, calculate subject results, apply the correct grading-scale snapshot, pass the result through the approval workflow, and write to the existing official result compatibility layer only after finalization.

**Acceptance criteria:** CBT results are linked to the correct student record and subject; practice attempts cannot enter official results; official results cannot be published before required approval.

### Slice 10.6 — Invigilation and integrity reporting

Add active candidate monitoring, heartbeats, disconnects, focus changes, failed code attempts, timeout events, suspicious answer similarity, unusual timing, watermarking, and post-exam reports. Full-screen and copy-blocking controls may be used as deterrents but must not be treated as security boundaries.

**Acceptance criteria:** examination officers can monitor active attempts; audit events are append-only; suspicious indicators are visible without automatically accusing a student; high-stakes deployments can use an approved secure-browser integration if required.

## 15. Phase 11 — core operational frontend modules

Migrate each module through the shared service and policy layer. The order can be adjusted after the pilot, but each slice should have its own feature flag and acceptance test.

### Slice 11.1 — Student and parent portals

Harden existing student and parent pages and move them onto the new portal shell and API. Support attendance, timetable, homework, fees, results, notices, certificates, transport, tickets, and parent-child switching.

### Slice 11.2 — Teacher portal

Provide assigned classes, subjects, timetable, roster, attendance, homework, materials, lessons, examinations, CBT practice, leave, notices, messages, and permitted result workflows. Teachers must not see the full school administration navigation.

### Slice 11.3 — Admissions and student records

Move inquiries, applications, admissions, student profiles, guardians, enrollment, transfers, documents, and bulk import to the frontend. Add file ownership and validation rules.

### Slice 11.4 — Attendance and timetable

Add mobile-friendly attendance, absence and late statuses, teacher assignments, timetable conflicts, approvals, and reports. Respect locked periods and school policies.

### Slice 11.5 — Notices, communication, and leave

Move notices, events, birthdays, messages, tickets, staff leave, parent communication, email, SMS, and push notifications into frontend workflows with provider health status.

### Slice 11.6 — Library, transport, hostel, gate pass, and certificates

Migrate these operational domains only after school, student, parent, role, file, audit, and finance foundations are stable. Each domain must be independently activatable and school-scoped.

**Acceptance criteria for Phase 11:** each module works from the frontend, respects permissions and context, has audit behavior, supports mobile use, survives a plugin upgrade, and can be disabled without deleting records.

## 16. Phase 12 — Elementor and Gutenberg integrations

### Slice 12.1 — Gutenberg dynamic blocks

Register blocks for login, dashboards, registration, notices, exams, results, invoices, and CBT launchers. Use dynamic server rendering, editor-safe placeholders, `block.json`, scoped styles, and runtime capability checks.

Never store private student or finance data in block attributes or post content.

**Acceptance criteria:** blocks work in classic and block themes, do not expose private data in the editor, and degrade safely when a module is inactive.

### Slice 12.2 — Elementor widgets

Register optional widgets for login, dashboards, registration, exams, results, fees, notices, and CBT. Load them only when Elementor is active and use the same frontend services as the portal.

Editor preview should show safe placeholders rather than real private records.

**Acceptance criteria:** Elementor can be deactivated without a fatal error; widgets work in editor and frontend contexts; responsive controls and theme styles remain intact.

### Slice 12.3 — Editor compatibility regression suite

Test current supported WordPress, Gutenberg, Elementor, classic themes, block themes, Full Site Editing templates, caching, multilingual behavior, mobile layout, and portal authentication.

**Acceptance criteria:** the compatibility matrix is published; each supported combination passes smoke and privacy tests.

## 17. Phase 13 — upgrade, security, privacy, and release hardening

### Slice 13.1 — Upgrade and migration testing

Test fresh installs, upgrades from the current archive, upgrades from at least two intermediate releases, interrupted migrations, large schools, multiple schools, scheduled tasks, existing shortcodes, translations, REST clients, payment callbacks, and uploaded files.

### Slice 13.2 — Security review

Review authentication, authorization, nonce and CSRF protection, SQL safety, output escaping, REST permission callbacks, file uploads, downloads, secret masking, rate limiting, brute-force protection, school isolation, parent-child isolation, result locking, CBT timing, and audit integrity.

### Slice 13.3 — Privacy and data governance

Define retention, export, correction, deletion, consent, document access, result visibility, parent access, staff access, audit retention, and high-risk settings policies. Add privacy-aware logs and error messages.

### Slice 13.4 — Performance and scale testing

Test large schools, simultaneous CBT attempts, report generation, exports, notifications, scheduled invoice generation, database indexes, cache invalidation, and mobile networks. CBT load testing must measure question delivery, answer saves, heartbeats, and auto-submit at realistic concurrency.

### Slice 13.5 — Release pipeline and support package

Build locked release archives with versioned assets and dependencies. Publish upgrade notes, module compatibility, supported WordPress and PHP versions, rollback instructions, known limitations, translation changes, and support procedures.

The release pipeline must assemble one installable Edutech v1.0 plugin ZIP. It must include the core and approved modules, exclude development files and secrets, validate the main plugin header, run migrations and smoke tests on a clean WordPress installation, and produce a checksum and release manifest.

**Acceptance criteria:** the release archive can be installed through the WordPress Plugins screen; enabled modules load from the same package; the package passes clean-install and upgrade tests; no source credentials or development artifacts are included.

## 18. Phase 14 — resilience, migration, governance, and scale additions

The following gaps were identified after the initial roadmap was prepared. They are now explicit roadmap work rather than informal recommendations.

### Slice 14.1 — Backup, restore, and disaster recovery

Create a frontend recovery center for the designer/platform owner. Support encrypted database and file backups, scheduled backups, retention rules, backup verification, staging restore, pre-migration snapshots, and documented rollback. Operational users must not be able to download or restore unrestricted backups.

**Acceptance criteria:** a backup can be verified and restored into staging; a failed migration has a known recovery path; backup access is audited; restore operations cannot silently overwrite production data.

### Slice 14.2 — Data import and migration center

Create guided imports for students, parents, teachers, staff, classes, subjects, fees, attendance, historical results, question banks, and curriculum data. Include field mapping, preview, validation, duplicate detection, staging, rollback, error reports, and import history.

**Acceptance criteria:** an import cannot publish invalid data directly; every import can be reviewed before commit; failed imports leave no partial records unless explicitly resumed; imported records retain source and batch metadata.

### Slice 14.3 — Progressive Web App and mobile readiness

Prepare the portal for installation as a Progressive Web App with responsive layouts, push-notification registration, safe read caching, connection status, and controlled offline drafts. Keep official CBT, final results, approvals, payments, and permissions server-authoritative. This slice provides the foundation for the native Android and iOS API programme.

**Acceptance criteria:** the portal can be installed on supported mobile browsers; cached data is scoped and revocable; offline drafts have conflict rules; no high-risk operation is finalized offline.

### Slice 14.4 — Localization and multi-language support

Add language selection, right-to-left support, local date and number formats, timezones, phone validation, address formats, academic terminology, grading labels, and localized report templates. Separate platform translations from school-defined labels.

**Acceptance criteria:** supported languages work in frontend, emails, reports, Gutenberg blocks, Elementor widgets, and mobile API responses; changing locale does not corrupt stored numeric or date values.

### Slice 14.5 — Promotion, repetition, resit, and graduation engine

Add configurable progression rules for minimum subjects passed, attendance thresholds, conditional promotion, repeat classes, carryover subjects, resits, graduation eligibility, and approved promotion lists. Store the rule version and decision history with each outcome.

**Acceptance criteria:** primary, secondary, tertiary, and vocational institutions can configure different progression policies; a promotion decision is approved and auditable; historical decisions do not change when current rules change.

### Slice 14.6 — Safeguarding, welfare, and restricted student support

Add a highly restricted module for medical data, emergency contacts, counseling, safeguarding incidents, special educational needs, accessibility requirements, bullying reports, disciplinary records, and follow-up tasks. This module must use separate capabilities and field-level privacy rules.

**Acceptance criteria:** welfare records are invisible to ordinary teachers, students, parents, and staff unless explicitly authorized; access and downloads are audited; sensitive notifications do not reveal private details.

### Slice 14.7 — Document and certificate verification

Add verification numbers, QR codes, public verification pages, revocation status, version history, digital-signature support, and authorized downloads for certificates, transcripts, receipts, report cards, and result slips.

**Acceptance criteria:** a verifier can confirm authenticity without viewing unrelated student data; revoked or superseded documents are clearly identified; every generated version remains traceable.

### Slice 14.8 — Strong authentication and device management

Add two-factor authentication, trusted-device management, active-session lists, remote logout, recovery codes, login history, suspicious-login alerts, and stronger policies for designers, administrators, finance users, examination officers, and result approvers.

**Acceptance criteria:** a user can revoke a lost device; high-risk roles can be required to use two-factor authentication; session and recovery events are audited.

### Slice 14.9 — System health and observability center

Create a health center showing plugin and database versions, migration status, PHP and WordPress compatibility, scheduled-task failures, email and SMS status, payment webhooks, storage usage, API health, error trends, and CBT concurrency status.

**Acceptance criteria:** the designer can identify failed services without inspecting server files; health checks distinguish warnings from blocking failures; sensitive credentials are never shown.

### Slice 14.10 — Global search and command center

Add permission-aware search across students, parents, teachers, invoices, results, attendance, exams, CBT attempts, questions, documents, and tickets. A command interface may provide shortcuts, but every result and action must still pass normal authorization and school-scope checks.

**Acceptance criteria:** search never reveals records from another school or linked family; indexed fields exclude secrets; destructive actions require their normal workflow confirmation.

### Slice 14.11 — Calendar and configurable workflow engine

Create a unified calendar for sessions, terms, semesters, exams, CBT windows, classes, events, holidays, leave, appointments, payment deadlines, assignments, and transport. Add configurable approval workflows for admissions, leave, fees, results, certificates, and amendments.

**Acceptance criteria:** calendar entries respect timezone and school context; workflow states are auditable; required approvals cannot be bypassed through API, mobile, AJAX, or direct URLs.

### Slice 14.12 — Public API, webhooks, and integration governance

Document the versioned API and add signed webhooks for admissions, payments, result publication, CBT submission, user suspension, invoice creation, and other approved events. Add delivery retries, idempotency, endpoint secrets, event logs, and provider health.

**Acceptance criteria:** webhook retries do not duplicate actions; consumers can verify signatures; administrators can revoke an integration; API and webhook access is scoped by module and school.

### Slice 14.13 — Inventory, procurement, library, and transport safety

Extend operational modules with inventory, procurement, supplier records, assets, barcode circulation, book copies, vehicle inspections, driver compliance, pickup authorization, route attendance, maintenance, and incident records where the institution requires them.

**Acceptance criteria:** each operational domain can be activated independently; assets, books, vehicles, and student assignments are school-scoped; safety and maintenance records have reminders and audit history.

### Slice 14.14 — Privacy, consent, and governance center

Provide consent controls for communications, student photos, medical data, video participation, public results, data exports, third-party services, and marketing. Add retention rules, export requests, correction requests, deletion restrictions, and role-specific privacy policies.

**Acceptance criteria:** the school can see which consent is required, granted, withdrawn, or expired; sensitive data retention is configurable; privacy operations are recorded and cannot bypass locked academic records.

### Slice 14.15 — Controlled AI assistance, deferred until governance is ready

Add AI only after data governance, consent, audit, and human-review workflows are stable. Permitted uses may include lesson-plan drafts, practice-question drafts, duplicate-question detection, translation, performance summaries, and revision suggestions. AI must never approve results, change locked grades, or make unreviewed high-impact decisions.

**Acceptance criteria:** AI output is labelled as generated, reviewable, and traceable; sensitive data is minimized; teachers or authorized staff approve content before official use; the module can be disabled without affecting core records.

### Slice 14.16 — Single-package assembly and release manifest

Create the final Edutech v1.0 packaging contract. The repository should contain one plugin entry point and one build command that assembles the installable ZIP. The package should contain the core platform and approved modules, while module activation and licensing remain runtime controls rather than separate manual installations.

The release manifest must list the Edutech version, internal database version, included modules, module versions, required WordPress and PHP versions, migration range, bundled dependencies, translation status, compatibility matrix, and checksum. The build must fail if a required module is missing, a dependency is unresolved, a secret is detected, or a production asset is absent.

**Acceptance criteria:** the output is one installable plugin ZIP; activation creates no duplicate tables; enabled modules register correctly; disabled modules remain dormant; the package can upgrade an existing supported installation without data loss; the manifest is stored with the release tag.

## 19. Git repository and release workflow

The Edutech v1.0 source must be maintained in one Git repository. The repository should contain source code, migrations, module manifests, tests, build scripts, documentation, and release configuration. Generated ZIP files should be release artifacts, not the only copy of the source.

Use protected branches for production releases. Every slice should be implemented in a focused branch or pull request, reviewed, tested, and merged before the next dependent slice begins. Commit messages should identify the phase and slice, for example `feat(slice-7.3): add score approval workflow`.

The repository must not contain API keys, payment secrets, customer exports, production uploads, private backups, or generated environment files. Use a template configuration file and secret-management instructions instead.

The first Git push requires a confirmed remote repository URL and credentials with permission to push. After that connection is available, the release process should push source commits and tags, while the installable ZIP is attached to a release or stored in the approved distribution location.

**Acceptance criteria:** the repository has a clean status before release; the Edutech v1.0 tag points to the tested commit; the single plugin ZIP is reproducible from that tag; secrets scanning passes; the remote contains the committed source and release metadata.

## 20. Definition of done for every slice

A slice is complete only when the following conditions are satisfied:

- The user outcome is available from the frontend.
- The designer/platform-owner exception is preserved.
- The module is gated by activation state and capability.
- School, campus, session, class, subject, and object boundaries are enforced.
- The service layer is shared by the frontend and any temporary legacy adapter.
- Forms have validation, loading, error, retry, empty, and success states.
- The layout works on mobile and desktop.
- Keyboard and screen-reader behavior is acceptable.
- Sensitive data is not exposed to unauthorized users or editor previews.
- Audit events exist for sensitive actions.
- Database changes have a versioned migration.
- Upgrade and rollback behavior has been tested.
- Automated tests or repeatable staging test scripts pass.
- Documentation explains configuration, permissions, and known limitations.
- The feature flag and dependency declaration are correct.
- The slice status is recorded as `accepted` only after its acceptance evidence is attached to the release checklist.
- If the slice is deferred, the reason, dependency, and planned release are recorded explicitly.

## 21. Recommended release sequence

The first production milestone should not attempt to deliver every module. It should deliver a reliable foundation:

1. Compatibility and migration baseline.
2. Brand service and design system.
3. Frontend authentication.
4. Central authorization.
5. WordPress admin restriction.
6. School, session, and parent-child context.
7. Setup wizard and automatic pages.
8. Student, parent, teacher, and school-admin portal shell.
9. Secure assessment and approval workflow.
10. Basic examination migration.
11. Practice CBT.
12. Backup, restore, and data import.
13. Official CBT only after question allocation, timing, audit, and result finalization pass load and security tests.
14. PWA and mobile API readiness.
15. Promotion, graduation, document verification, localization, health, privacy, workflow, and integration governance.
16. Single-package assembly, release manifest, Git tag, and installable Edutech v1.0 ZIP.

Do not make official CBT the first feature to migrate. It depends on nearly every foundation: identity, school scope, student records, grading, audit, frontend routing, rate limiting, server timing, and result approval.

## 22. Critical risks and controls

### Risk: rebranding breaks existing sites

Preserve internal identifiers and use compatibility aliases. Change public brand first and defer internal namespace migration.

### Risk: frontend pages become insecure admin copies

Require every operation to call the centralized service and authorization layer. Direct page rendering must never be treated as authorization.

### Risk: approved scores are changed silently

Use state transitions, immutable snapshots, database-level update guards in the service layer, append-only audit events, cryptographic tamper evidence, and amendment versions.

### Risk: CBT candidates receive identical questions

Validate pool capacity before opening the exam, allocate candidate snapshots server-side, configure overlap policy, and preserve the exact attempt snapshot.

### Risk: CBT timing is manipulated

Use server timestamps, server deadlines, bounded grace, idempotent auto-submit, and audited overrides. Never trust the browser clock.

### Risk: country support becomes hard-coded

Separate core engines from country packs, curriculum packs, examination-body configurations, and institution overrides. Version every pack.

### Risk: school data crosses boundaries

Apply school, campus, session, class, subject, parent-child, and object ownership checks to every read, mutation, export, download, report, and scheduled operation.

### Risk: Elementor or Gutenberg exposes private data

Use dynamic server-rendered content and editor-safe placeholders. Never embed private records in post content or block attributes.

## 23. Final target state

When the roadmap is complete, a new school should be able to:

1. Install the core plugin and optional add-ons.
2. Run a guided setup wizard without editing code.
3. Select country, institution type, academic calendar, currency, modules, and grading template.
4. Configure schools, campuses, levels, classes, subjects, departments, and programmes.
5. Create or import staff, teachers, students, and parents.
6. Give all operational users frontend access.
7. Restrict WordPress admin to the designer/platform owner.
8. Configure CA1, CA2, examination, project, practical, or other assessment components so their weights match the school policy.
9. Require teacher submission and administrator approval before results become final.
10. Keep finalized scores locked and handle corrections through amendments.
11. Create practice CBT examinations.
12. Create official CBT examinations with unique question sets, exam codes, subject timers, question timers, server-controlled deadlines, audit records, and approved result publication.
13. Generate results, report cards, transcripts, certificates, invoices, and reports in the school’s currency and branding.
14. Use the system through WordPress, Gutenberg, Elementor, or the built-in portal without losing data during upgrades.

The practical implementation principle is:

> **Slice by dependency, release by acceptance criteria, and protect historical records by versioning every important configuration and result.**

## References

[1]: file:///home/ubuntu/work/school-management-analysis/frontend-first-plugin-gap-assessment.md "Frontend-first plugin gap assessment"
[2]: file:///home/ubuntu/work/school-management-analysis/cbt-engine-specification.md "CBT engine specification"
[3]: file:///home/ubuntu/work/school-management-analysis/rebranding-and-editor-compatibility-plan.md "Rebranding and WordPress, Elementor, and Gutenberg compatibility plan"
[4]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/admin/inc/WLSM_Database.php "Current plugin database schema and migration code"
[5]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/admin/inc/school/staff/examination/WLSM_Staff_Examination.php "Current examination and result management code"
[6]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/includes/helpers/WLSM_M_Role.php "Current staff role and permission model"
[7]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/includes/helpers/WLSM_M_User.php "Current student and parent identity helpers"
[8]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/public/public.php "Current public bootstrap, frontend hooks, and asset loading"
[9]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/public/api/WLSM_Api.php "Current REST API handlers"
