# [RBAC service](../README.md) > [Models](README.md) > UserKeysModel

The `Bayfront\BonesService\Rbac\Models\UserKeysModel` is used to manage user API keys.

This model uses the [HasOmittedFields](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/hasomittedfields.md) and [Prunable](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/prunable.md) traits.

The `rbac.user.key.created`, `rbac.user.key.updated` and `rbac.user.key.deleted` [events](../events.md) are executed.

The `key_value` field is automatically created as a 36 character alphanumeric string.
It is returned once when the resource is created. If the value is misplaced, a new key must be created.

The `expires_at` field cannot be greater than the `user.key.max_mins` [config value](../setup.md#configuration).
If not defined on creation, the value will be defined using the `user.key.max_mins` value. 
This field cannot be updated once created.

Allowed fields write:

- `user`: ([User ID](users.md)) Required, string
- `name`: Required, string, max length 255
- `allowed_domains`: Array
- `allowed_ips`: Array
- `expires_at`: Datetime

Unique fields:

- `user` + `name`

Allowed fields read:

- `id`
- `user`
- `name`
- `allowed_domains`
- `allowed_ips`
- `expires_at`
- `last_used`
- `created_at`
- `updated_at`

Omitted fields:

- `key_value`

Model-specific methods include:

- [findByKey](#findbykey)

## findByKey

**Description:**

Find user key by key value.

**Parameters:**

- `$key` (string)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`