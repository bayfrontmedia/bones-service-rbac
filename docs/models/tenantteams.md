# [RBAC service](../README.md) > [Models](README.md) > TenantTeamsModel

The `Bayfront\BonesService\Rbac\Models\TenantTeamsModel` is used to manage tenant teams.

Allowed fields write:

- `tenant`: ([Tenant ID](tenants.md)) Required, string
- `name`: Required, string, max length 255
- `description`: String, max length 255

Unique fields:

- `tenant` + `name`

Allowed fields read:

- `id`
- `tenant`
- `name`
- `description`
- `created_at`
- `updated_at`

Model-specific methods include:

- [findByName](#findbyname)

## findByName

**Description:**

Find tenant team by tenant ID and name.

**Parameters:**

- `$tenant_id` (string)
- `$name` (string)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`