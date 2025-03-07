# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

- `[Unreleased]` for upcoming features.
- `Added` for new features.
- `Changed` for changes in existing functionality.
- `Deprecated` for soon-to-be removed features.
- `Removed` for now removed features.
- `Fixed` for any bug fixes.
- `Security` in case of vulnerabilities

## [1.1.0] - 2025.03.07

### Changed

- Updated dependencies
- Updated `User` class so admin users inherit all existing permissions for every tenant
- Updated documentation

### Removed

- Removed `SoftDeletes` trait from the following resource models:
  - `PermissionsModel`
  - `TenantInvitationsModel`
  - `TenantMetaModel`
  - `TenantRolesModel`
  - `TenantTeamsModel`
  - `TenantUserMetaModel`
  - `UserKeysModel`
  - `UserMetaModel`

## [1.0.0] - 2025.01.09

### Added

- Initial release