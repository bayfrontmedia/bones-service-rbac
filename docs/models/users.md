# [RBAC service](../README.md) > [Models](README.md) > UsersModel

The `Bayfront\BonesService\Rbac\Models\UsersModel` is used to manage users.

This model uses the [HasOmittedFields](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/hasomittedfields.md) and [SoftDeletes](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/softdeletes.md) traits.

On creation, a unique salt is created for the user, and the password is hashed using the salt.
The password can be filtered (for example, to enforce password requirements) using the `rbac.user.password` [filter](../filters.md).
If the password is updated, the `rbac.user.password.updated` [event](../events.md) is executed.
Extreme care should be taken to ensure the password is never leaked or stored.
If the user exists as a [tenant owner](tenants.md), they cannot be deleted.

When updating the `meta` field, new keys will be merged with existing keys.
Setting a value to `null` will remove the key.

This service only checks if the user is enabled and verified for [authentication](../authentication/README.md).
Any other use-cases should be handled at the app-level

Allowed fields write:

- `email`: Required, string, max length 255
- `password`: Required, string, max length 255
- `meta`: array
- `admin`: Boolean
- `enabled`: Boolean

Unique fields:

- `email`

Allowed fields read:

- `id`
- `email`
- `meta`
- `admin`
- `enabled`
- `created_at`
- `updated_at`
- `verified_at`

Omitted fields:

- `password`
- `salt`

Model-specific methods include:

- [findByEmail](#findbyemail)
- [verify](#verify)
- [unverify](#unverify)
- [deleteUnverified](#deleteunverified)

## findByEmail

**Description:**

Find user by email.

Can be used with the `SoftDeletes` trait trashed filters.

**Parameters:**

- `$email` (string)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## verify

**Description:**

Update `verified_at` field to current datetime.

The `rbac.user.verified` [event](../events.md) is executed.

**Parameters:**

- `$email` (string)

**Returns:**

- (bool)

## unverify

**Description:**

Update `verified_at` field to `null.

**Parameters:**

- `$email` (string)

**Returns:**

- (bool)

## deleteUnverified

**Description:**

Soft-delete all unverified users created and never updated,
or last updated before timestamp.

**Parameters:**

- `$timestamp` (int)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`