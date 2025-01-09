# [RBAC service](../README.md) > [Models](README.md) > TenantUserMetaModel

The `Bayfront\BonesService\Rbac\Models\TenantUserMetaModel` is used to manage tenant user metadata.

This model uses the [HasProtectedPrefix](../traits/hasprotectedprefix.md) and [SoftDeletes](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/softdeletes.md) traits.

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

Model-specific methods include:

- [findByKey](#findbykey)

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