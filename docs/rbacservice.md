# [RBAC service](README.md) > RbacService class

The `Bayfront\BonesService\Rbac\RbacService` class contains the following Bones services:

- [ORM service](https://github.com/bayfrontmedia/bones-service-orm)

Methods include:

- [getConfig](#getconfig)
- [getTableName](#gettablename)
- [createHash](#createhash)
- [hashMatches](#hashmatches)
- [createTotp](#createtotp)
- [totpIsExpired](#totpisexpired)

## getConfig

**Description:**

Get RBAC service configuration value in dot notation.

**Parameters:**

- `$key = ''` (string): Key to return in dot notation
- `$default = null` (mixed): Default value to return if not existing

**Returns:**

- (mixed)

## getTableName

**Description:**

Get prefixed table name.

**Parameters:**

- `$table` (string): Valid `TABLE_*` constant

**Returns:**

- (string)

## createHash

**Description:**

Create hash from raw value.

**Parameters:**

- `$raw_value` (string)

**Returns:**

- (string)

## hashMatches

**Description:**

Does hash match raw value?

**Parameters:**

- `$hash` (string)
- `$raw_value` (string)

**Returns:**

- (bool)

## createTotp

**Description:**

Create TOTP (Time-based one-time password).
Returns raw value. How the TOTP is handled is determined at the app-level.

**Parameters:**

- `$duration = 0` (int): Validity duration, in minutes. `0` for no expiration.
- `$length = 6` (int)
- `$type = self::TOTP_TYPE_NUMERIC`: Any `TOTP_TYPE_*` constant

Constants include:

- `TOTP_TYPE_NONZERO`
- `TOTP_TYPE_ALPHA`
- `TOTP_TYPE_NUMERIC`
- `TOTP_TYPE_ALPHANUMERIC`
- `TOTP_TYPE_ALL`

**Returns:**

- [Totp](totp.md)

## totpIsExpired

**Description:**

Is TOTP expired?

**Parameters:**

- `$totp` ([Totp](totp.md))

**Returns:**

- (bool)