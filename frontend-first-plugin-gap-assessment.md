# Edutech v1.0 — Frontend-First Transformation Assessment

## Executive decision

The requested Edutech v1.0 direction is achievable, but it is **not a login-form change**. It is a product and architecture transformation of the supplied School Management Pro codebase.

The plugin already has a partial frontend account area for students and parents. It also contains a large backend feature set for school administrators and staff. However, the current implementation assumes that administrators and staff will operate through WordPress admin pages, WordPress admin menus, `admin-ajax.php`, and admin-specific assets. Teachers are not represented as a separate top-level identity in the current role model; they are generally employees or staff with role permissions and class or subject restrictions.

The correct Edutech v1.0 target is:

> **All operational users authenticate and work from a branded frontend application. WordPress admin remains restricted to the technical designer/owner role for plugin installation, updates, emergency recovery, and platform-level maintenance.**

The frontend must become a secure application shell with role-aware navigation, capability enforcement, school and session context, responsive workflows, audit logging, and consistent APIs. The existing WordPress admin implementation should remain available during migration as a controlled fallback, but ordinary school operations should progressively disappear from the dashboard.

## Current state versus requested state

| Area | Current implementation | Required target |
|---|---|---|
| Student login | WordPress authentication exposed through a student-oriented widget and frontend account pages | Branded frontend login with role-aware redirect and secure session handling |
| Parent login | Parent account screens exist, but the login presentation is not a complete unified parent portal | Parent login with household/student selector and explicit child data boundaries |
| Teacher login | No dedicated teacher frontend application; staff workflows are primarily WordPress admin pages | Teacher portal with classes, subjects, attendance, homework, timetable, exams, notices, leave, and messaging |
| School administrator login | Staff/admin identity is tied to WordPress users and admin menus | Frontend school-admin portal with permissions and school switcher |
| Designer role | WordPress administrator is the natural technical owner | Keep WordPress admin for the designer only; block operational roles from `wp-admin` |
| Configuration | School settings, payment, SMS, email, Firebase, Zoom, roles, templates, and setup are admin pages | Frontend configuration center with capability gates, masking, validation, and audit logs |
| Navigation | Admin menu pages and query-string account actions | Typed frontend routes with role-specific sidebar or mobile navigation |
| Data access | Mix of frontend templates, admin handlers, AJAX, direct SQL, and REST endpoints | A single service/API layer with shared authorization and response contracts |
| Authorization | Staff permissions exist, but route and handler enforcement is distributed | Central policy engine applied to every route, mutation, object, school, and session |
| UI system | Existing PHP templates, Bootstrap-era styles, jQuery, and feature-specific CSS | One responsive design system with accessible components and predictable states |

## Current roles and identity model

The current code does not model all requested users as one clean role enum.

- The role helper defines `admin` and `employee` for school staff. `employee` is the broad staff category that can represent teachers, accountants, librarians, transport staff, and other employees.
- Custom staff roles are stored with school-specific permissions. A staff member can also have restrictions such as assigned class, assigned subjects, or assigned tickets.
- Students are identified through student records linked to WordPress users. The student portal resolves identity from the current WordPress user.
- Parents are identified through parent-to-student relationships. The current implementation supports an active student context for parents, which is useful but must be made explicit and secure in the new portal.
- The technical designer is effectively the WordPress administrator or plugin owner. This role should be treated as a platform-level role and should not be mixed with school operational roles.

The target model should retain compatibility with these records but introduce an explicit application identity and access context:

| Target role | Source compatibility | Main scope |
|---|---|---|
| Designer | WordPress administrator or explicitly designated platform owner | Entire WordPress installation and platform maintenance |
| Network/school administrator | Existing staff record with admin role | One or more assigned schools, subject to school scope |
| Teacher | Existing employee/staff record plus teacher profile or teaching assignments | Assigned classes, sections, subjects, and sessions |
| Accountant | Existing employee/staff record and finance permissions | Assigned school and finance scope |
| Librarian | Existing employee/staff record and library permissions | Assigned school and library scope |
| Transport/hostel/support staff | Existing employee/staff record and feature permissions | Assigned school and feature scope |
| Parent/guardian | Existing parent relationship | Own account and linked children only |
| Student | Existing student record | Own academic and account data only |

A staff member may have one primary title but multiple capabilities. The portal should use **capability-based access** rather than hard-coding every screen to a job title.

## Major gaps that must be closed

### 1. A unified frontend authentication system is missing

The current widget uses `wp_login_form()` and is described as a student login form. This does not provide a complete product login experience for students, parents, teachers, school administrators, and staff. Login redirect behavior is handled through WordPress hooks, and suspended-student handling is implemented in the public bootstrap.

The new login system needs a branded login page with username or email support, password visibility control, remember-me behavior, lost-password flow, reset-password flow, account suspension messaging, invalid-login rate limiting, optional two-factor authentication for staff and administrators, and role-aware redirect. It must also prevent open redirects by allowing only approved internal destinations.

The frontend login should use normal WordPress authentication and secure cookies rather than duplicating password storage. A custom REST or AJAX login endpoint can be added only if it uses WordPress authentication APIs, nonce or equivalent CSRF protection, throttling, and secure response handling.

### 2. Operational users can still reach WordPress admin

Moving the visual login to the frontend is insufficient if teachers, students, parents, or school administrators can still access `wp-admin` or execute admin actions directly.

The target must include:

- `admin_init` and `login_redirect` policies that deny or redirect non-designer operational users from `wp-admin`.
- Explicit exceptions for safe WordPress endpoints such as logout, password reset, media upload callbacks if required, and emergency recovery.
- A capability check on every admin page and AJAX handler during migration.
- A clear policy for the school administrator who needs configuration access but must not become a platform administrator.
- Protection against direct access to `admin-post.php`, `admin-ajax.php`, and legacy page URLs.

The designer must remain able to access WordPress admin without being redirected into the operational portal.

### 3. Teacher frontend portal does not exist as a complete product

The largest functional gap is the teacher experience. Existing staff modules cover many teacher-relevant operations, but they are rendered through admin menu pages and permission checks. A teacher frontend must be designed and implemented for:

- Teacher profile and availability.
- Assigned school, session, classes, sections, and subjects.
- Daily and period-based timetable.
- Student roster with class/subject restrictions.
- General and subject attendance.
- Homework creation, publishing, editing, grading, and feedback.
- Study materials and lesson management.
- Live classes and meeting links.
- Exams, papers, marks, grades, remarks, and result submission.
- Leave requests and approval status.
- Notices, events, birthdays, and school messages.
- Tickets and support communication.
- Notifications and unread counts.

The teacher portal must not expose the full staff menu. Its navigation must be generated from verified capabilities and assignment scope.

### 4. School administrator frontend configuration is not implemented

The current configuration is spread across admin settings screens. The frontend replacement needs a configuration center with clearly separated areas:

- School profile, contact details, logo, address, timezone, locale, currency, and academic identity.
- Academic sessions, classes, sections, subjects, activities, houses, mediums, and student types.
- Admission and registration rules.
- Student and parent account creation policies.
- Staff accounts, assignments, custom roles, and permissions.
- Attendance rules and late/absence behavior.
- Examination settings, grading criteria, result templates, and publishing controls.
- Fee types, invoices, discounts, concessions, payment methods, receipts, and finance rules.
- Email, SMS, push notification, Firebase, and template settings.
- Transport routes, vehicles, drivers, hostels, rooms, library, gate pass, tickets, and certificates.
- Appearance and branding settings.
- Import/export, backups, logs, and data-retention controls.

Sensitive integrations must be presented only to authorized administrators, with secret masking, reauthentication for high-risk changes, validation, test-connection actions, and audit history.

### 5. Existing admin AJAX and page handlers need a frontend service layer

The current application has a very large number of admin AJAX actions and admin page handlers. Reusing them directly from the frontend would preserve the coupling to `admin-ajax.php`, admin URLs, legacy HTML responses, and inconsistent authorization.

The preferred migration is:

1. Extract business operations from page templates and AJAX callbacks.
2. Put each operation behind a service method with typed input and output.
3. Add REST endpoints or a versioned application API that calls those services.
4. Keep the old AJAX handlers as compatibility adapters during the transition.
5. Move one domain at a time to the frontend.

The API must not simply expose the existing handlers. It needs stable response envelopes, consistent error codes, pagination, filtering rules, validation schemas, and explicit object ownership checks.

### 6. Authorization must be centralized and object-aware

The existing role system is valuable but distributed. A permission name such as `view_students` is not enough. The application must also answer:

- Which school is the user operating in?
- Which academic session is active?
- Is the user active in that school?
- Is the user assigned to the class, section, subject, ticket, or record?
- Does the requested student belong to the parent?
- Does the requested invoice belong to the student or permitted school?
- Is the user allowed to view, create, edit, publish, approve, or delete the resource?
- Is the action safe to perform from the current workflow state?

Create a policy service with methods such as `can_view_student`, `can_edit_attendance`, `can_publish_result`, `can_manage_settings`, and `can_switch_school`. Every frontend route, REST endpoint, legacy AJAX adapter, export, print view, and download must call the same policy service.

The current route-level pattern of checking only `is_user_logged_in()` must not be used as the sole API permission check for operational endpoints.

### 7. Parent-to-child context switching needs stronger guarantees

Parents commonly have multiple children. The existing user model supports an active student ID. In the new portal, the selected child must be stored and validated server-side for every request. The client must never be trusted merely because it sends a `student_id`.

The parent portal should provide a visible child switcher and show the selected child in the page header. Every financial, attendance, exam, homework, certificate, ticket, and transport query must verify that the selected student is linked to the current parent and belongs to an active permitted school and session.

### 8. School and session context switching must become a first-class frontend feature

Staff can be assigned to multiple schools and use user metadata to select a current school. Staff also use a current session. These are currently integrated with admin URLs and admin-side screens.

The frontend must provide:

- A school switcher only when the user is assigned to multiple active schools.
- A session switcher constrained to sessions the user may access.
- Clear context in the header and page title.
- Server-side validation on every request.
- Cache invalidation when the context changes.
- Protection against cross-school and cross-session data leakage.

### 9. Data-entry workflows need redesign rather than template copying

Admin forms are designed for wide desktop screens and WordPress admin interaction. Copying them into frontend pages will produce a poor and unsafe experience. Each workflow needs a UX review covering:

- Multi-step admissions.
- Bulk student import.
- Attendance entry on mobile.
- Homework creation and submission.
- Exam mark entry and validation.
- Invoice generation and payment approval.
- Staff and custom-role creation.
- School setup wizard.
- Transport and hostel assignments.
- Certificate and ID-card generation.
- Data exports and destructive actions.

Every form should define draft, validation, save, failure, retry, and completion states. Destructive actions need an explicit confirmation step and a readable impact summary.

### 10. The design system is not yet a complete frontend application system

The plugin has public CSS and dashboard styles, but the requested product needs a coherent application shell rather than a collection of admin-like pages. The new system should define:

- Responsive layout for desktop, tablet, and mobile.
- Sidebar and bottom navigation rules by role.
- Typography scale and color tokens.
- Cards, tables, forms, drawers, dialogs, alerts, badges, tabs, pagination, date pickers, and upload controls.
- Loading skeletons, empty states, error states, and offline or retry messaging.
- Accessible focus states, keyboard navigation, labels, contrast, and screen-reader text.
- A consistent print mode for invoices, receipts, attendance sheets, certificates, admit cards, and results.
- Localization and right-to-left support where already supported by the language files.

The interface should avoid exposing raw WordPress terminology such as “admin page,” “post,” or “AJAX error.”

### 11. Notification and background operations need frontend status handling

The plugin schedules invoice generation and sends SMS, email, push, and third-party notifications. These operations may be slow or fail after a user submits a form.

The frontend must show operation status, prevent duplicate submissions, expose retry behavior, and record audit outcomes. Scheduled jobs need logs that administrators can view from the frontend. Payment callbacks and notification providers must be verified independently of browser success messages.

### 12. Sensitive settings and privacy controls need strengthening

The application handles student identity, family contact details, academic results, attendance, fees, payment credentials, SMTP credentials, SMS credentials, Firebase credentials, and video-provider credentials.

The frontend transformation must add:

- Secret masking and never-return-secret API responses.
- Capability and school-scoped settings access.
- Reauthentication for payment and credential changes.
- Audit logging for account, role, fee, result, attendance, and configuration changes.
- Export restrictions and download authorization.
- File type, size, MIME, and ownership checks for photos, homework, study materials, certificates, and documents.
- Retention and deletion rules.
- Privacy-conscious error messages and logs.

## Target frontend architecture

The target can remain a WordPress plugin, but it should behave like a frontend application layered over WordPress.

### 1. Frontend application shell

Create a dedicated frontend portal page, for example `/school-portal/`, with a short-code or template entry point. The shell should resolve the current user, role, school, session, permissions, and available navigation. It should redirect unauthenticated users to the branded login page and redirect authenticated users to their role dashboard.

Use stable routes rather than query-string actions. Examples include:

```text
/school-portal/login
/school-portal/dashboard
/school-portal/teacher/classes
/school-portal/teacher/attendance
/school-portal/parent/children
/school-portal/student/results
/school-portal/admin/students
/school-portal/admin/settings/fees
/school-portal/profile
/school-portal/logout
```

The implementation may use server-rendered PHP initially, a modern JavaScript layer incrementally, or a hybrid. The important requirement is a stable routing and service boundary.

### 2. Application API

Add a versioned REST namespace such as `/wp-json/wlsm/v1/portal`. Endpoints should be grouped by domain and should return JSON rather than HTML fragments. Each endpoint needs:

- Authentication check.
- Role and capability check.
- School and session context check.
- Resource ownership or assignment check.
- Input schema and type validation.
- Pagination and bounded filters.
- Consistent success and error response format.
- Audit event for sensitive mutations.

The existing API can continue to support mobile clients, but the portal API should not inherit unclear permissions or legacy response formats without review.

### 3. Shared domain services

Extract services such as:

- `AuthService` and `SessionContextService`.
- `AuthorizationService`.
- `StudentService` and `ParentService`.
- `StaffService` and `RoleService`.
- `AttendanceService`.
- `HomeworkService` and `StudyMaterialService`.
- `ExaminationService`.
- `FinanceService`.
- `NotificationService`.
- `FileService`.
- `AuditService`.
- `MigrationService`.

The existing admin page and AJAX callbacks should call these services during migration. This prevents the frontend and old dashboard from implementing different business rules.

### 4. WordPress admin restriction

The designer role should retain normal WordPress access. Operational users should be redirected to the frontend portal and denied from WordPress admin screens. The restriction should be implemented as a policy, not as a visual redirect only.

The policy must account for:

- Direct URL access.
- AJAX and REST requests.
- Cron and webhook requests.
- Media and password-reset workflows.
- Emergency support access.
- Multisite or network-admin scenarios if supported.

## Role dashboard design

### Student dashboard

The student landing page should prioritize today’s timetable, attendance summary, upcoming exams, homework due dates, notices, fees, live classes, and quick access to study materials. It should use a mobile-first layout and avoid exposing parent-only or staff-only actions.

### Parent dashboard

The parent landing page should show the selected child, attendance, fees and payment status, recent results, homework, notices, timetable, transport, tickets, and upcoming events. The child selector must be prominent and every child-specific view must show the current child.

### Teacher dashboard

The teacher landing page should show today’s classes, pending attendance, homework awaiting review, upcoming exams, unread messages, leave status, and assigned tickets. The primary action should be “take attendance” or the next teaching task rather than a generic statistics wall.

### School administrator dashboard

The administrator landing page should show school health metrics, admissions, attendance alerts, outstanding fees, recent payments, staff actions, pending approvals, upcoming exams, and system notifications. Configuration should be accessible through grouped settings and not hidden behind WordPress terminology.

### Designer dashboard

The designer may remain in WordPress admin. A lightweight link to the operational portal should be available for testing, but the designer should not be forced into the operational portal for plugin maintenance.

## Migration roadmap

### Phase 0 — safety baseline

Before moving screens, establish a staging copy and define the supported roles, schools, sessions, and critical workflows. Add database backups, error logging, audit events, and a rollback plan. Inventory every admin page, AJAX action, REST route, shortcode, file download, print view, and scheduled task.

### Phase 1 — identity and access foundation

Implement the branded frontend login, logout, password recovery, role-aware redirect, account suspension handling, school switcher, session switcher, parent child selector, and WordPress admin restriction for operational roles. Add automated access tests before migrating business screens.

### Phase 2 — shared frontend shell

Create the portal page, frontend routing, responsive layout, navigation builder, design tokens, notifications, profile, settings, error handling, and capability-aware menu rendering. Do not migrate every feature yet; first make the application shell reliable.

### Phase 3 — student and parent hardening

Reuse the existing student and parent features but move them behind the new API and policies. Replace query-string student selection with server-validated context. Add payment, downloads, tickets, homework, and child switching tests.

### Phase 4 — teacher portal

Migrate the teacher-critical workflows first: dashboard, timetable, class roster, attendance, homework, study materials, notices, leave, and exams. Keep finance, system settings, and school-wide administration out of teacher navigation regardless of frontend URL knowledge.

### Phase 5 — school administrator portal

Migrate admissions, students, classes, sections, staff, roles, attendance oversight, exams, fees, payments, reports, notices, transport, library, hostel, certificates, and school configuration. Use the existing staff permissions as input but enforce them through the centralized policy service.

### Phase 6 — configuration and operations

Move integrations, templates, setup wizard, branding, logs, imports, exports, backups, and notification controls. Add confirmation, reauthentication, audit history, and test-connection workflows for sensitive settings.

### Phase 7 — controlled retirement of operational admin

After production monitoring proves parity, disable the relevant admin menus and legacy operational URLs for non-designer users. Keep adapters and emergency recovery tools behind a feature flag until the migration is complete.

## Acceptance criteria for “working perfectly”

The system should not be considered complete when the frontend pages merely render. It should meet the following acceptance criteria:

1. Students, parents, teachers, staff, and school administrators can log in from one branded frontend entry point.
2. The designer can still use WordPress admin for platform maintenance.
3. Operational users cannot use WordPress admin pages or legacy AJAX actions to bypass frontend permissions.
4. Every API and form mutation enforces role, capability, school, session, and object scope.
5. Parents can access only their linked children and can switch children safely.
6. Teachers can access only assigned schools, sessions, classes, sections, subjects, and permitted workflows.
7. School administrators can configure their school from the frontend without obtaining platform-level WordPress privileges.
8. Password recovery, suspension, logout, session expiry, and invalid-login behavior are clear and secure.
9. Finance, results, attendance, admissions, and role changes create auditable records.
10. All major workflows work on mobile and desktop with keyboard and screen-reader support.
11. Loading, empty, validation, failure, retry, and success states are implemented for every important screen.
12. Critical workflows have automated tests and staging test scripts.
13. Scheduled invoice generation, payments, notifications, and file generation expose reliable status and error logs.
14. Existing records, passwords, school assignments, custom roles, and parent-child links remain compatible during migration.
15. The designer can enable or disable frontend modules using controlled feature flags during rollout.

## Recommended first implementation slice

The safest first build is a **frontend identity and shell release**, not a full migration of every module. It should include:

- `/school-portal/login`.
- WordPress authentication with role-aware redirect.
- Operational-user block for `wp-admin`.
- Dashboard shell with responsive sidebar and mobile navigation.
- Student, parent, teacher/staff, and school-admin route guards.
- School/session/child context services.
- Profile, password change, logout, and notification center.
- Central authorization service.
- Audit logging for login, logout, context changes, and permission failures.
- Feature flags that allow each migrated module to be enabled gradually.

Once this foundation passes security and usability testing, the teacher portal should be the next priority because it is the largest missing role experience. Student and parent screens should then be migrated onto the same service and design system rather than maintained as a separate legacy frontend.

## Final recommendation

Proceed with the frontend-first direction, but preserve the existing WordPress data model and business logic during the first stages. Do not duplicate the entire plugin in a new application and do not copy admin templates directly into frontend pages. Build a shared authorization and service layer, introduce the frontend shell, migrate role by role, and retire operational WordPress admin access only after parity and security tests pass.

The designer exception is appropriate. The designer should remain the only routine operational user with WordPress dashboard access. All other roles should use the frontend for authentication, daily work, school configuration, reports, finance, communication, and account management.

## References

[1]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/includes/helpers/WLSM_M_Role.php "Current staff roles and permission model"
[2]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/includes/helpers/WLSM_M_User.php "Current student and parent identity helpers"
[3]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/includes/helpers/WLSM_Login.php "Current login redirect helper"
[4]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/public/inc/WLSM_Shortcode.php "Current public shortcode and frontend entry-point implementation"
[5]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/public/inc/widgets/WLSM_Login_Widget.php "Current student-oriented login widget"
[6]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/public/inc/account/index.php "Current frontend account dispatcher"
[7]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/public/public.php "Public bootstrap, AJAX hooks, REST registration, and login behavior"
[8]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/admin/admin.php "Admin AJAX registration and backend operational hooks"
[9]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/admin/inc/WLSM_Menu.php "WordPress admin menu and staff dashboard implementation"
[10]: file:///home/ubuntu/work/school-management-analysis/project/school-management-pro/public/api/WLSM_Api.php "Current REST API routes and handlers"
