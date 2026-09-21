# ADR-001: Edutech v1.0 Backend and Notification Architecture

- **Status:** Accepted
- **Date:** 2026-09-21
- **Product:** Edutech v1.0

## Decision

Edutech v1.0 will keep the existing WordPress plugin database as the system of record during the frontend-first transformation and initial mobile-app release. A fresh, environment-separated Firebase setup will provide push-notification infrastructure for Android, iOS, and web clients. Native mobile applications will consume a secured Edutech REST API rather than connecting directly to the WordPress database.

Supabase will not be introduced into the current production migration. It will remain a future proof-of-concept option for a later relational backend or mobile platform migration after real traffic, data-model, performance, and operational requirements have been measured.

## Rationale

The current plugin already stores the school domain data in WordPress, including schools, students, parents, teachers, classes, fees, results, and examinations. Keeping WordPress as the source of truth avoids immediate data duplication and synchronization conflicts. It also allows the frontend-first plugin work and the mobile API work to proceed without a simultaneous database migration.

Firebase is appropriate for the immediate notification requirement because the current code already integrates the Firebase Admin SDK for messaging. A fresh Firebase environment allows Edutech to rotate old credentials, separate development from production, and register new mobile applications without changing the WordPress records.

Supabase remains technically attractive for a future Edutech SaaS platform because its PostgreSQL and row-level security model suit highly relational school data. Introducing it now, however, would create a second source of truth, identity synchronization, webhook reliability, conflict resolution, migration, and operational responsibilities before the current authorization and data model have been hardened.

## Target architecture

```text
WordPress database
        |
Edutech v1.0 plugin and frontend dashboards
        |
Secure Edutech REST API
        |
Native Android and iOS applications
        |
Fresh Firebase projects for push notifications
```

The WordPress database remains authoritative for academic, financial, identity, and CBT records. Firebase stores notification infrastructure data only unless a later, separately approved decision expands its role.

## Environment policy

Use separate Firebase projects for development, staging, and production. Do not reuse production credentials in local development or staging. Firebase service-account credentials must remain outside Git, outside the plugin ZIP, outside public uploads, and outside mobile application binaries.

The plugin must receive Firebase configuration through protected server configuration or environment variables. The service-account JSON must not be committed to the repository. Device notification tokens must be treated as replaceable credentials and must be revocable per user and device.

## Consequences

This decision minimizes immediate data-loss and synchronization risk and allows mobile work to begin after the REST authorization model is complete. It also means that WordPress performance, API scalability, background processing, caching, and rate limiting must be strengthened before a large mobile rollout.

A future Supabase migration would require a new relational schema, identity strategy, migration tooling, reconciliation reports, dual-write or cutover planning, and a rollback plan. It must not be started by copying live data into a second system without an approved migration design.

## Acceptance criteria

The decision is considered implemented when the following are complete:

1. Development, staging, and production Firebase projects are created under the Edutech organization.
2. Old Firebase credentials are not present in Git, the plugin package, public uploads, or application binaries.
3. The plugin reads Firebase credentials from protected server configuration.
4. Firebase device-token registration, rotation, revocation, and notification delivery are tested.
5. The Edutech API enforces authentication, school scope, object ownership, rate limits, and audit logging.
6. Native Android and iOS clients use the API and never connect directly to WordPress tables.
7. WordPress backup, restore, migration, and rollback tests pass before production mobile release.
8. A separate Supabase proof of concept is approved only after performance and scale evidence justifies it.
