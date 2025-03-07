# [RBAC service](../README.md) > [Models](README.md) > TenantMetaModel

The `Bayfront\BonesService\Rbac\Models\TenantMetaModel` is used to manage tenant metadata.

This model uses the [HasProtectedPrefix](../traits/hasprotectedprefix.md) trait.

Due to the way the field is stored in the database, meta values will always be returned as a string.
By JSON-encoding and decoding the value, other data types can be preserved.

Allowed fields write:

- `tenant`: ([Tenant ID](tenants.md)) Required, string
- `meta_key`: Required, string, max length 255
- `meta_value`: Required, max length 4000000000

Unique fields:

- `tenant` + `meta_key`

Allowed fields read:

- `id`
- `tenant`
- `meta_key`
- `meta_value`
- `created_at`
- `updated_at`

Model-specific methods include:

- [findByKey](#findbykey)

## findByKey

**Description:**

Find tenant meta by tenant ID and meta key value.

**Parameters:**

- `$tenant_id` (string)
- `$meta_key` (string)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`