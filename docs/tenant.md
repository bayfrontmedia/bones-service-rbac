# [RBAC service](README.md) > Tenant

The `Bayfront\BonesService\Rbac\Tenant` class is used to represent a single tenant.

Its constructor requires an [RbacService](rbacservice.md) instance, and an [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) representing a single [tenant](models/tenants.md).

Methods include:

- [getResource](#getresource)
- [asObject](#asobject)
- [read](#read)
- [get](#get)
- [getId](#getid)
- [getOwner](#getowner)
- [getDomain](#getdomain)
- [getName](#getname)
- [getMeta](#getmeta)
- [isEnabled](#isenabled)
- [getTenantMeta](#gettenantmeta)

## getResource

**Description:**

Get tenant resource.

**Parameters:**

- (none)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

## asObject

**Description:**

Get entire tenant as an object

**Parameters:**

- (none)

**Returns:**

- (object)

## read

**Description:**

Get entire tenant array.

**Parameters:**

- (none)

**Returns:**

- (array)

## get

**Description:**

Get single tenant field.

**Parameters:**

- `$field` (string)
- `$default = null` (mixed): Default value to return

**Returns:**

- (mixed)

## getId

**Description:**

Get tenant ID.

**Parameters:**

- (none)

**Returns:**

- (mixed)

## getOwner

**Description:**

Get owner user ID.

**Parameters:**

- (none)

**Returns:**

- (string)

## getDomain

**Description:**

Get domain.

**Parameters:**

- (none)

**Returns:**

- (string)

## getName

**Description:**

Get name.

**Parameters:**

- (none)

**Returns:**

- (string)

## getMeta

**Description:**

Get meta key in dot notation, or default value if not existing.

**Parameters:**

- `$key` (string)
- `$default = null` (mixed)

**Returns:**

- (mixed)

## isEnabled

**Description:**

Is tenant enabled?

**Parameters:**

- (none)

**Returns:**

- (bool)

## getTenantMeta

**Description:**

Get tenant meta by meta key, or `NULL` if not existing.

**Parameters:**

- `$meta_key` (string)

**Returns:**

- (string|null)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`