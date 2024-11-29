# [RBAC service](README.md) > RbacService class

The `Bayfront\BonesService\Rbac\RbacService` class contains the following Bones services:

- [ORM service](https://github.com/bayfrontmedia/bones-service-orm)

Methods include:

- [getConfig](#getconfig)
- [getTableName](#gettablename)

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



