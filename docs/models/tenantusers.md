# [RBAC service](../README.md) > [Models](README.md) > TenantUsersModel

The `Bayfront\BonesService\Rbac\Models\TenantUsersModel` is used to manage tenant users.

The `rbac.tenant.user.created`, `rbac.tenant.user.updated` and `rbac.tenant.user.deleted` [events](../events.md) are executed.

The tenant owner cannot be removed as a tenant user.

Allowed fields write:

- `tenant`: ([Tenant ID](tenants.md)) Required, string
- `user`: ([User ID](users.md)) Required, string

Unique fields:

- `tenant` + `user`

Allowed fields read:

- `id`
- `tenant`
- `user`
- `created_at`
- `updated_at`

Model-specific methods include:

- [findByUserId](#findbyuserid)
- [userInTenant](#userintenant)
- [tenantUserInTenant](#tenantuserintenant)

## findByUserId

**Description:**

Find tenant user by tenant and user ID.

**Parameters:**

- `$tenant_id` (string)
- `$user_id` (string)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## userInTenant

**Description:**

Is user in tenant?

**Parameters:**

- `$tenant_id` (string)
- `$user_id` (string)

**Returns:**

- (bool)

## tenantUserInTenant

**Description:**

Is tenant user in tenant?

**Parameters:**

- `$tenant_id` (string)
- `$tenant_user_id` (string)

**Returns:**

- (bool)