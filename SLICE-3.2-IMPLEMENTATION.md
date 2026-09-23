# Edutech v1.0 — Slice 3.2 Implementation Record

## Status

**Implemented and locally tested; production role-assignment acceptance is still required before this slice is marked complete.**

## Delivered

Edutech now includes a centralized `Edutech_Identity` service that normalizes frontend identity across students, parents, teachers, staff, school administrators, examination officers, designers, and unknown users.

The service derives a canonical role without deleting or replacing existing WordPress roles or WLSM staff records. It also captures school scope, current school, session scope, student record ID, parent-linked student IDs, staff permissions, authentication state, and the designer exception.

The canonical role keys are:

| Role key | Meaning |
|---|---|
| `student` | User linked to a WLSM student record. |
| `parent` | User linked to one or more student records through the WLSM parent relationship. |
| `teacher` | WordPress user with the explicit teacher role. |
| `staff` | Employee or staff user without school-admin classification. |
| `school_admin` | WLSM school admin or administrator with school-management scope. |
| `exam_officer` | Examination officer role. |
| `designer` | Edutech designer exception; not treated as a normal school operator. |
| `unknown` | Authenticated or unauthenticated identity that cannot yet be classified. |

The existing `WLSM_M_Role` helper remains the source of legacy staff-school assignments and permissions. `Edutech_Identity` acts as the normalization adapter for new frontend modules.

## Frontend integration

The existing localized `wlsm-public` script now receives a safe `edutechIdentity` object containing only role-aware frontend fields:

```js
{
  authenticated: true,
  role: "student",
  roleLabel: "Student",
  schoolId: 3,
  sessionId: 42,
  studentId: 77,
  parentStudentIds: [],
  isDesigner: false
}
```

Portal pages also receive a role body class such as `edutech-role-student`, allowing future navigation and dashboard modules to show role-specific experiences without copying identity logic into every template.

## Compatibility guarantees

No WordPress roles were renamed. No WLSM staff records, student records, parent relationships, school IDs, or session records were migrated. Existing admin behavior remains available. The service uses legacy helpers when present and safely returns an `unknown` context when a record cannot be resolved.

## Verification

- Identity-context compatibility test passes for all canonical role mappings.
- Student school scope and student record mapping pass.
- Edutech smoke tests pass.
- **557 first-party PHP files pass syntax validation.**
- Static security scan passes.
- REST permission coverage remains 282 routes: 281 centralized protected routes and one intentional public settings route.
- JWT, feature-flag, portal-wrapper, and authentication compatibility tests pass.
- Single-plugin ZIP builds and passes integrity checks.
- Identity service and frontend localization wiring are included in the package.
- Git diff whitespace validation passes.

## Production acceptance still required

Create and verify real test accounts for every role in a staging WordPress environment. Confirm multiple schools per staff user, school switching, current-session switching, parent users with multiple children, teachers assigned to sections, examination officers, suspended students, inactive schools, administrators without WLSM school assignments, and the designer exception. Confirm that frontend context never exposes staff permissions or unrelated school data to the browser.

## Rollback

Deploy the previous commit and remove the identity localization and body-class adapter if necessary. The legacy WLSM role helper and all persisted records remain unchanged, so rollback does not require data restoration.
