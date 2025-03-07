# [RBAC service](../README.md) > [Models](README.md) > PermissionsModel

The `Bayfront\BonesService\Rbac\Models\PermissionsModel` is used to manage global permissions.

Allowed fields write:

- `name`: Required, string, max length 255
- `description`: String, max length 255

Unique fields:

- `name`

Allowed fields read:

- `id`
- `name`
- `description`
- `created_at`
- `updated_at`

Model-specific methods include:

- [findByName](#findbyname)

## findByName

**Description:**

Find permission by name.

**Parameters:**

- `$name` (string)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`