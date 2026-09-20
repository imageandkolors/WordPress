# Edutech v1.0 — Native Android and iOS Migration Plan Without Data Loss

## Executive recommendation

The safest path is to keep the existing WordPress plugin as the **system of record** and build native Android and iOS applications as API clients. The mobile apps should not connect directly to the WordPress database and should not create a second independent copy of students, grades, invoices, or CBT results.

The architecture should be:

```text
Native Android app ─┐
                    ├── Versioned mobile/API layer ─── Shared domain services ─── Existing WordPress data
Native iOS app ─────┘

Frontend portal ────────────────┘
```

This allows the existing frontend portal, native Android app, and native iOS app to use the same authentication, authorization, school scope, grading, finance, examination, CBT, and audit rules.

> **Do not migrate the database into the mobile apps. Migrate access to the data through a stable API.**

## What happens to the current data

Existing data remains in the plugin database. That includes users, student records, parent relationships, schools, sessions, classes, subjects, fees, payments, examinations, grades, files, question banks, CBT attempts, and audit records.

The mobile apps retrieve data through API endpoints. When a mobile user submits an attendance record, score, payment confirmation, CBT answer, or profile update, the API validates the request and writes it through the same service layer used by the frontend.

This means that installing or updating the mobile apps does not require copying or converting existing records.

## Required API foundation

The current plugin already has REST API handlers, but an Edutech v1.0 mobile-ready platform requires a new versioned API boundary. Create a namespace such as:

```text
/wp-json/edutech/v1/mobile
```

The legacy API should remain available for existing clients during migration. Do not remove or change existing routes without a compatibility period.

The new API should provide consistent JSON responses, stable error codes, pagination, filtering, validation, rate limits, idempotency, version headers, and explicit permission callbacks.

Every endpoint must verify:

- Authenticated user identity.
- User role and capability.
- School and campus scope.
- Academic session and period scope.
- Class and subject assignment.
- Parent-child relationship.
- Object ownership.
- Current workflow state.
- Module activation state.

The API must never trust a school ID, student ID, role, grade, price, deadline, or approval status supplied by the mobile client.

## Mobile authentication

The apps should use a secure token-based session system while preserving WordPress user accounts and passwords.

A recommended flow is:

1. The user logs in through the mobile API using the existing WordPress identity.
2. The server verifies the password through WordPress authentication APIs.
3. The server issues short-lived access and refresh tokens.
4. The app stores tokens in Android Keystore or iOS Keychain.
5. Access tokens are rotated or expired regularly.
6. Refresh tokens can be revoked by the user or administrator.
7. Logout revokes the mobile session.
8. Password changes revoke existing sessions according to policy.

Do not store WordPress passwords in the mobile app. Do not use a permanent API key shared by every installation or every student.

The platform should support device registration, active-session listing, remote logout, suspicious-login alerts, optional multi-factor authentication, and separate policies for students, parents, teachers, finance users, examination officers, and administrators.

## Shared service layer before native apps

Before building the native apps, extract the important operations from WordPress templates and AJAX handlers into shared services. The frontend portal, REST API, and temporary legacy admin adapters should all call these services.

Important services include:

- Authentication and mobile-session service.
- User and role service.
- School and session context service.
- Authorization and object-policy service.
- Student and parent service.
- Teacher and staff service.
- Attendance service.
- Timetable service.
- Homework and materials service.
- Assessment and result service.
- Finance and payment service.
- Examination service.
- CBT attempt and timer service.
- Notification service.
- File and document service.
- Audit service.

This is the main protection against inconsistent behavior between the website and mobile apps.

## Data synchronization model

The first mobile release should use a **server-authoritative request model**. The app loads current data from the API and submits changes to the server. It should not attempt to synchronize the entire database.

For read data, use:

- Pagination.
- Incremental updates.
- `updated_since` or cursor-based synchronization.
- ETags or version headers.
- Cache headers where appropriate.
- Local read cache with an explicit freshness policy.

For write operations, use:

- Idempotency keys.
- Client-generated request IDs.
- Server timestamps.
- Optimistic concurrency versions.
- Clear retry rules.
- Conflict responses when the record changed on the server.

A request that is retried because of poor mobile connectivity must not create duplicate attendance entries, payments, score submissions, CBT answers, or approvals.

## Recommended synchronization records

Add a server-side change log or sync cursor service for mobile clients. It can record:

```text
entity_type
entity_id
school_id
session_id
operation
version
changed_at
changed_by
```

The mobile app can request changes after a cursor instead of downloading all student or school data repeatedly.

Do not send sensitive data to a mobile device merely because it is present in the local WordPress database. The API should return only records permitted for that user, school, context, and module.

## Offline behavior

Offline support should be introduced carefully.

Suitable offline data includes:

- Cached timetable.
- Cached notices.
- Cached read-only student information.
- Teacher attendance drafts.
- Practice CBT drafts where the school explicitly allows it.

High-risk operations should remain server-authoritative:

- Official CBT answers and deadlines.
- Final grades.
- Result approvals.
- Payments.
- Account permissions.
- Student transfers.
- Published results.
- Financial adjustments.

For offline attendance drafts, the app should queue a signed request with the original capture time and device ID. When connectivity returns, the API validates the user’s assignment, school context, period, and duplicate status. It may reject stale or conflicting submissions.

Official high-stakes CBT should not rely on ordinary offline synchronization. If offline or restricted-network CBT is required, it should be a separate secure-exam product with controlled device enrollment, encrypted local storage, secure time policy, invigilation, and a formal reconciliation process.

## Mobile-specific endpoint examples

The API should provide domain endpoints rather than exposing database tables directly:

```text
POST /mobile/v1/auth/login
POST /mobile/v1/auth/refresh
POST /mobile/v1/auth/logout
GET  /mobile/v1/me
GET  /mobile/v1/context
POST /mobile/v1/context/school
POST /mobile/v1/context/student
GET  /mobile/v1/dashboard
GET  /mobile/v1/students/{id}
GET  /mobile/v1/attendance
POST /mobile/v1/attendance/batches
GET  /mobile/v1/timetable
GET  /mobile/v1/results
GET  /mobile/v1/invoices
POST /mobile/v1/payments/intents
GET  /mobile/v1/cbt/practice
POST /mobile/v1/cbt/official/attempts
POST /mobile/v1/cbt/attempts/{id}/answers
POST /mobile/v1/cbt/attempts/{id}/submit
```

The exact route names can change, but the principle should remain: the API exposes business actions and views, not raw SQL tables.

## Handling grading and approved scores on mobile

Teachers may enter draft scores from the mobile app only if the school permits it. The server must apply the same assessment scheme, score validation, approval workflow, locking, and amendment rules as the frontend portal.

The app must not calculate the final grade as the authoritative value. It may display a preview, but the server must recalculate:

```text
CA1 raw score
+ CA2 raw score
+ examination raw score
→ configured weighted total
→ grading scale
→ grade and status
```

After submission, the teacher’s app should show the batch as read-only. Reviewers and administrators should see the appropriate approval actions. A finalized result cannot be edited through the mobile app. Corrections use the amendment workflow and preserve the previous version.

## Handling CBT on mobile

Practice CBT is suitable for Android and iOS when the app uses the same question bank and attempt service as the web portal.

The official CBT mobile experience should include:

- Candidate authentication.
- Exam code or candidate token.
- Eligibility validation.
- Server-generated question snapshot.
- Per-question and per-subject timers from server deadlines.
- Answer autosave.
- Reconnect policy.
- Server-side marking.
- Audit events.
- Final result workflow.

The app must never receive future answer keys or rely on the phone clock for official deadlines. The official exam attempt must be linked to the existing student record and must remain visible in the normal result and audit systems.

If a student changes devices, the server should either resume the existing attempt according to the exam policy or reject the new device and require invigilator approval. A second device must not create a second active attempt.

## File and document delivery

Student photos, certificates, receipts, report cards, study materials, and question media should be served through authorized download endpoints or signed, expiring URLs. The app should not receive permanent public file URLs for private records.

The server must verify user, school, student, document, and file permissions before issuing the download. Download events should be audited for sensitive documents.

## Push notifications

Add a notification service that supports Android and iOS push providers without embedding provider-specific logic in every module. Notifications should be event-driven and permission-aware.

Examples include:

- Fee invoice created.
- Payment confirmed.
- Homework assigned.
- Attendance alert.
- Result published.
- CBT scheduled.
- CBT result published.
- Parent notice.
- Leave request decision.

Store notification preferences and delivery status. Do not include sensitive grades, invoice amounts, or medical information in an unprotected push preview unless the school explicitly accepts that risk.

## App release and backward compatibility

The API and app must evolve independently.

Use these rules:

- Mobile apps declare the minimum API version they require.
- The server supports older mobile clients for a defined period.
- Breaking changes receive a new API version.
- New response fields are additive where possible.
- Removed fields are deprecated before removal.
- The app receives a minimum-supported-version response when an upgrade is required.
- The server can remotely disable a vulnerable app version.
- Feature flags control new modules and workflows.
- The frontend portal remains a fallback while a mobile release is pending.

A mobile app update should never require a database reset. Database migrations remain a server-side plugin responsibility.

## App architecture choice

A native Android app can be built with Kotlin and a native iOS app with Swift. That provides the strongest platform integration but requires two codebases.

A cross-platform client such as Flutter or React Native can reduce development time while still producing Android and iOS apps. The choice should be made after the API contract and design system are stable. It should not influence the WordPress database architecture.

The recommended sequence is:

1. Build and stabilize the mobile API.
2. Build a small internal client or API test app.
3. Build the student and parent mobile MVP.
4. Add teacher attendance and timetable.
5. Add notifications and profile/session management.
6. Add fees and payments.
7. Add practice CBT.
8. Add approved score entry if required.
9. Add official CBT only after dedicated security and load testing.

## Migration with no data loss

Use a parallel rollout rather than replacing the website:

1. Keep the WordPress plugin database and frontend portal active.
2. Add the new versioned API without changing existing tables destructively.
3. Run read-only mobile pilots first.
4. Add mobile writes for low-risk workflows such as profile preferences and notices.
5. Add attendance and practice submissions with idempotency and conflict handling.
6. Add finance, grading, and official exam workflows only after approval and audit tests pass.
7. Compare mobile and portal outputs during a controlled pilot.
8. Expand by school or cohort.
9. Keep the web portal as a fallback.
10. Retire legacy API routes only after all clients have migrated.

Never perform a one-time export/import as the primary mobile migration method. That creates two sources of truth and eventually produces conflicting grades, balances, and student records.

## Mobile rollout phases

### Mobile Slice M1 — API readiness

Create the versioned API namespace, consistent response format, authorization policies, rate limits, audit events, API documentation, and automated endpoint tests.

### Mobile Slice M2 — Authentication and context

Implement mobile sessions, token rotation, device registration, remote logout, user profile, school switching, academic session context, and parent-child switching.

### Mobile Slice M3 — Read-only student and parent MVP

Deliver dashboard, timetable, attendance history, notices, homework, results, invoices, and profile views. Do not add high-risk write operations until read data matches the portal.

### Mobile Slice M4 — Teacher and staff operations

Add teacher timetable, roster, attendance drafts, homework, notices, leave, and permitted communication. Apply assignment and school policies on every write.

### Mobile Slice M5 — Finance and notifications

Add payment intents, payment status, receipts, fee notifications, push registration, and reconciliation. Use idempotent payment operations and signed document downloads.

### Mobile Slice M6 — Practice CBT

Add practice question delivery, attempts, timers, answer storage, results, explanations, and analytics. Practice mode must remain separate from official academic results.

### Mobile Slice M7 — Secure official CBT

Add official exam codes, candidate-specific snapshots, question uniqueness, subject and question deadlines, device and reconnect policy, invigilator controls, and official result finalization. This slice requires separate concurrency, security, and examination acceptance tests.

### Mobile Slice M8 — App-store release and operations

Prepare privacy policies, app permissions, crash reporting, monitoring, support procedures, minimum app version control, release notes, and staged Android and iOS rollout.

## Acceptance criteria for a safe mobile upgrade

The mobile programme is ready for production when:

1. The existing WordPress frontend still works.
2. No data is copied into a second authoritative database.
3. Existing users can log into the app using their existing identities.
4. School, session, class, subject, and parent-child boundaries are enforced.
5. Mobile writes use the same services and approval rules as the portal.
6. Duplicate retries do not create duplicate records.
7. Offline drafts have explicit conflict and expiry rules.
8. Finalized grades, payments, published results, and official CBT records cannot be altered from the app.
9. Private documents use authorized, expiring downloads.
10. App and API versions can be upgraded independently.
11. Old supported app versions receive a safe migration or upgrade message.
12. The system can revoke a device or session.
13. Mobile API activity appears in audit reports.
14. Load testing passes for expected concurrent users and CBT attempts.
15. A mobile release can be rolled back without rolling back server data destructively.

## Final recommendation

Build the mobile apps as a new client layer over the reworked platform, not as a replacement for the plugin database. The current WordPress plugin should first become the secure backend and API provider. The frontend portal and mobile apps should then consume the same versioned services.

This approach allows the project to add Android and iOS gradually, keep the current website active, preserve existing records, support staged pilots, and avoid data loss. It also ensures that grading approval, official examination records, fees, and CBT attempts remain consistent across web and mobile.

## References

[1]: file:///home/ubuntu/work/school-management-analysis/school-management-pro-phased-implementation-roadmap.md "Phased implementation roadmap"
[2]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/public/api/WLSM_Api.php "Current REST API handlers"
[3]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/includes/helpers/WLSM_M_Role.php "Current role and permission helper"
[4]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/includes/helpers/WLSM_M_User.php "Current student and parent identity helper"
[5]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/admin/inc/WLSM_Database.php "Current plugin database schema and migration code"
