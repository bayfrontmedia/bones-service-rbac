# [RBAC service](../README.md) > [Models](README.md) > TenantUserMetaModel

The `Bayfront\BonesService\Rbac\Models\TenantUserMetaModel` is used to manage tenant user metadata.

This model uses the [SoftDeletes](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/softdeletes.md) trait.

The `protected_prefix` [config value](../setup.md#configuration) is used to restrict access to meta keys beginning with a protected prefix.
This can be overridden using the [withProtectedPrefix](#withprotectedprefix) and [onlyProtectedPrefix](#onlyprotectedprefix) functions.

Due to the way the field is stored in the database, meta values will always be returned as a string.
By JSON-encoding and decoding the value, other data types can be preserved.

Allowed fields write:

- `tenant_user`: ([Tenant user ID](tenantusers.md)) Required, string
- `meta_key`: Required, string, max length 255
- `meta_value`: Required, max length 4000000000

Unique fields:

- `tenant_user` + `meta_key`

Allowed fields read:

- `id`
- `tenant_user`
- `meta_key`
- `meta_value`
- `created_at`
- `updated_at`
- `deleted_at`

Model-specific methods include:

- [withProtectedPrefix](#withprotectedprefix)
- [onlyProtectedPrefix](#onlyprotectedprefix)
- [getProtectedPrefix](#getprotectedprefix)
- [findByKey](#findbykey)

## withProtectedPrefix

**Description:**

Filter next query to include protected prefix.

**Parameters:**

- (none)

**Returns:**

- `self`

## onlyProtectedPrefix

**Description:**

Filter next query to include only protected prefix.

**Parameters:**

- (none)

**Returns:**

- `self`

## getProtectedPrefix

**Description:**

Get protected prefix.

**Parameters:**

- (none)

**Returns:**

- (string)

## findByKey

**Description:**

Find tenant user meta by tenant user ID and key value.

Can be used with the `SoftDeletes` trait trashed filters.

**Parameters:**

- `$tenant_user_id` (string)
- `$meta_key` (string)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`