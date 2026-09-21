# Edutech v1.0 — Slice 1.1 / 1.2 Implementation Record

## Status

**Implemented and locally tested. Staging acceptance is still required before this slice is marked accepted.**

## Delivered

The plugin now has a versioned Edutech foundation layer while preserving the legacy WLSM namespace and database tables.

The bootstrap loads an environment compatibility service, migration coordinator, and module registry. The compatibility service checks PHP, WordPress, database connectivity, required extensions, and WordPress filesystem availability. An incompatible runtime receives an actionable administrator notice instead of partially booting the public application.

The migration coordinator stores a database version and migration state in WordPress options. The initial migration is idempotent and creates only Edutech foundation options for the module registry and feature flags. It does not rename, delete, or rewrite legacy tables. Failed migrations record an error and can be retried on a later request.

The module registry supports stable module keys, versions, dependencies, persisted active/inactive state, activation checks, dependent-module protection during deactivation, module health checks, and registration hooks for future modules. Core remains active and cannot be deactivated. Module deactivation never deletes module data.

The installable package manifest now includes plugin, database, and module-registry versions. The smoke suite verifies the foundation classes and manifest contract.

## Verification

- **552 first-party PHP files pass syntax validation.**
- Edutech smoke tests pass.
- JWT compatibility test passes.
- Static security scan passes.
- REST permission coverage remains 282 routes: 281 centralized protected routes and one intentional public settings route.
- The single-plugin ZIP builds and passes integrity validation.
- Git diff whitespace validation passes.

## Compatibility policy

The legacy `WLSM_*` constants, options, tables, routes, shortcodes, and integrations remain unchanged. This slice adds new Edutech services around the existing system; it does not attempt a destructive rename or schema replacement.

## Staging acceptance still required

A WordPress staging site must verify fresh activation, upgrade activation from the current archive, repeated requests after an interrupted migration, database backup and restore, module registration, dependency failure, safe module deactivation, and recovery from a failed migration. The frontend designer control for module activation is not yet implemented; the registry API is the foundation for that future frontend slice.

## Rollback

Rollback is code rollback to the prior commit followed by restoring the WordPress database backup if a migration state was written incorrectly. The initial migration only adds Edutech options, so disabling the new bootstrap hooks and removing only those options is reversible. Legacy WLSM tables and records are not removed by this slice.
