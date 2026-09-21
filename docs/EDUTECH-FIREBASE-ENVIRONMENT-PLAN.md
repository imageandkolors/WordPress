# Edutech v1.0 — Fresh Firebase Environment Plan

## Approved scope

Firebase is approved for push notifications and notification-related mobile services. WordPress remains the authoritative database for school, student, parent, teacher, fee, academic, result, and CBT records.

## Required projects

| Environment | Suggested project | Purpose |
|---|---|---|
| Development | `edutech-dev` | Local development and disposable test devices. |
| Staging | `edutech-staging` | Release-candidate testing with non-production data. |
| Production | `edutech-prod` | Live Edutech notifications and registered mobile clients. |

The names are suggestions and should be finalized under the organization’s Firebase ownership policy.

## Fresh-resource checklist

Create new service accounts, service-account keys, Firebase Cloud Messaging configuration, Android application registrations, iOS application registrations, web-push configuration if required, notification topics, and API-key restrictions. Do not copy the old project’s private key into the new project or repository.

The new mobile package identifiers and iOS bundle identifiers should use the Edutech namespace. Development and staging builds must use their own Firebase configuration files and cannot silently point at production.

## Credential policy

Firebase credentials must be supplied to WordPress through protected server configuration or environment variables. They must not be stored in the Git repository, plugin ZIP, public uploads, JavaScript bundles, mobile binaries, issue comments, or CI logs.

The production service account should have the minimum Firebase permissions required for messaging. Service-account keys should be rotated on a defined schedule and revoked immediately after suspected exposure. Notification tokens are sensitive, revocable device credentials and should be stored with user and device ownership metadata.

## Migration sequence

First create the three new Firebase environments and configure restricted service accounts. Next add environment-based credential loading to the Edutech plugin and test notification delivery in staging. Then register new Android and iOS clients, deploy a staging mobile build, and verify token registration, token refresh, logout revocation, notification targeting, and failure handling.

For production, deploy the production credentials through the hosting secret store, release the mobile applications, monitor delivery and error rates, and allow the old Firebase project to remain available only for rollback observation. Revoke old service-account keys after the new production path has been verified and the rollback window has expired.

## Data and rollback

Changing the Firebase project does not delete WordPress school data. Existing mobile notification tokens should be considered invalid and re-registered. If Firebase Authentication or Firestore is found to contain live business data, it must be inventoried and migrated separately before the old project is retired.

Rollback means restoring the previous WordPress plugin configuration and old notification integration only while old credentials remain valid. No destructive deletion of the old Firebase project should occur until delivery, token refresh, and rollback tests are complete.

## Release gates

The fresh Firebase integration must not be released with hard-coded credentials, public service-account files, unrestricted API keys, unverified notification recipients, missing token revocation, or a direct mobile connection to WordPress tables. Production release also requires WordPress backups, API authorization tests, rate limiting, audit logging, and a tested rollback procedure.
