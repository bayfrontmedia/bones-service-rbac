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

## [1.3.2] - Upcoming

### Changed

- Updated model meta to not dot arrays

## [1.3.1] - 2025.06.18

### Added

- Added meta key validation for resources using `HasNullableJsonField` trait

## [1.3.0] - 2025.06.06

### Added

- Added `UserTokensModel` and related database migration
- Added ability for user to authenticate with multiple access and refresh tokens,
 allowing more than one simultaneous session per user
- Added `$new_users_only` parameter to `deleteUnverified` method
- Added `UserIdAuthenticator`
- Added the `rbac.tenant.owner.updated` event

### Changed

- Removed token-specific methods from `UserMetaModel` to `UserTokensModel`
- Updated documentation

### Removed

- Removed `SoftDeletes` trait from the following resource models:
  - `TenantsModel`
  - `UsersModel`
- Removed `admin.all_permissions` config key in favor of admins inheriting all existing permissions with the option
  of impersonating users.

## [1.2.0] - 2025.05.30

### Added

- Added `admin.all_permissions` config setting to define which tenant permissions are assigned to an admin user

## [1.1.3] - 2025.05.27

### Fixed

- Bugfix when checking user and tenant meta validation rules on create and update
- Bugfix in the query used in `deleteExpiredTotps` and `deleteExpiredTokens` methods

## [1.1.2] - 2025.05.20

### Fixed

- Bugfix in the query used in `deleteExpiredTotps` and `deleteExpiredTokens` methods

## [1.1.1] - 2025.05.03

### Fixed

- Bugfix in `deleteExpiredTotps` and `deleteExpiredTokens` methods 

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