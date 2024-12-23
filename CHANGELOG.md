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

## [1.0.0] - 2024.12.23

### Changed

- Updated dependencies

## [1.0.0-beta.1] - 2024.12.20

### Added

- Added `$required_fields` property to all models.
- Added `EmailAuthenticator` and related events.
- Added password request functions.
- Added `Totp` class and migrated user MFA functions to TOTP.
- Added `unverify` method to `UsersModel`.
- Added `rbac.user.email.updated` event.
- Added `rbac.token.authenticate` event.
- Added tenant invitation, role and team-related methods to `User` class.
- Added tenant-related events.
- Added support for multidimensional meta keys.

### Changed

- Renamed all models to include `Model` suffix.
- Updated `rbac.user.mfa.created` event to pass the user's email as a parameter.
- Updated user verifications to use `UserMetaModel`.

### Removed

- Removed `UserMfaAuthenticator` and related events.
- Removed user key functions from `UsersModel` in favor of `userTotp` methods in `UserMetaModel`.
- Removed `deleted_at` as a readable field for all models.

### Fixed

- Fixed bug when updating user key during authentication.
- Miscellaneous bugfixes

## [1.0.0-beta] - 2024.11.29

### Added

- Initial beta release.