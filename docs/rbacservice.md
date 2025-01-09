# [RBAC service](README.md) > RbacService class

The `Bayfront\BonesService\Rbac\RbacService` class contains the following Bones services:

- [ORM service](https://github.com/bayfrontmedia/bones-service-orm)

The following constants exist to define TOTP type when using the createTotp method:

- `TOTP_TYPE_NONZERO`
- `TOTP_TYPE_ALPHA`
- `TOTP_TYPE_NUMERIC`
- `TOTP_TYPE_ALPHANUMERIC`

Methods include:

- [getConfig](#getconfig)
- [getTableName](#gettablename)
- [createHash](#createhash)
- [hashMatches](#hashmatches)

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