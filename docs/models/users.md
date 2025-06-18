# [RBAC service](../README.md) > [Models](README.md) > UsersModel

The `Bayfront\BonesService\Rbac\Models\UsersModel` is used to manage users.

This model uses the [Castable](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/castable.md), [HasNullableJsonField](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/hasnullablejsonfield.md) and [HasOmittedFields](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/hasomittedfields.md) traits.

On creation, a unique salt is created for the user, and the password is hashed using the salt.
The password can be filtered (for example, to enforce password requirements) using the `rbac.user.password` [filter](../filters.md).
If the password is updated, the `rbac.user.password.updated` [event](../events.md) is executed.
Extreme care should be taken to ensure the password is never leaked or stored.
If the user exists as a [tenant owner](tenants.md), they cannot be deleted.

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

Delete all unverified users created and never updated.

NOTE: When $new_users_only is false, existing users who update their email address but have not yet
verified it will be removed.

**Parameters:**

- `$timestamp` (int)
- `$new_users_only = true` (bool): When `false`, users last updated before the timestamp will also be removed

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`