# [RBAC service](../README.md) > [Models](README.md) > TenantsModel

The `Bayfront\BonesService\Rbac\Models\TenantsModel` is used to manage tenants.

This model uses the [SoftDeletes](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/softdeletes.md) trait.

Upon successful creation, the owner will be added to the tenant as a tenant user.
If updating the tenant owner, the user must exist as a tenant user.
If the tenant owner is updated, the `rbac.tenant.owner.updated` [event](../events.md) will be executed.

This service only checks if the tenant is enabled when calculating [user permissions](../user.md#getpermissions).
Any other use-cases should be handled at the app-level

Allowed fields write:

- `owner`: ([User ID](users.md)) Required, string
- `domain`: Required, string, max length 63
- `name`: Required, string, max length 255
- `meta`: Array
- `enabled`: Boolean

Unique fields:

- `domain`

Allowed fields read:

- `id`
- `owner`
- `domain`
- `name`
- `meta`
- `enabled`
- `created_at`
- `updated_at`
- `deleted_at`

Model-specific methods include:

- [findByDomain](#findbydomain)
- [getOwnerId](#getownerid)

## findByDomain

**Description:**

Find tenant by domain.

Can be used with the `SoftDeletes` trait trashed filters.

**Parameters:**

- `$domain` (string)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getOwnerId

**Description:**

Get tenant owner user ID.

**Parameters:**

- `$tenant_id` (string)

**Returns:**

- (string)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`