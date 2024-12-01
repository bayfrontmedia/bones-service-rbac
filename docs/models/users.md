# [RBAC service](../README.md) > [Models](README.md) > UsersModel

The `Bayfront\BonesService\Rbac\Models\UsersModel` is used to manage users.

This model uses the [SoftDeletes](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/softdeletes.md) trait.

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
- `deleted_at`

Model-specific methods include:

- [findByEmail](#findbyemail)
- [verify](#verify)
- [deleteUnverified](#deleteunverified)
- [createMfa](#createmfa)
- [getMfa](#getmfa)
- [mfaIsValid](#mfaisvalid)
- [deleteMfa](#deletemfa)
- [deleteExpiredMfas](#deleteexpiredmfas)

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

## deleteUnverified

**Description:**

Soft-delete all unverified users created before timestamp.

**Parameters:**

- `$timestamp` (int)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## createMfa

**Description:**

Create MFA for non-deleted user, verifying MFA wait time has elapsed.

This method utilizes the `user.mfa.wait` and `user.mfa.duration` [config values](../setup.md#configuration).
The MFA value is returned once when the resource is created. If the value is misplaced, a new MFA must be created.

The `rbac.user.mfa.created` [event](../events.md) is executed on successful creation.
Extreme care should be taken to ensure the MFA value is never leaked or stored.

**Parameters:**

- `$email` (string)
- `$length = 6` (int)
- `$type = self::MFA_TYPE_NUMERIC`: Any `MFA_TYPE_*` constant

**Returns:**

- (array): Keys: `created_at`, `expires_at`, `value`

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException`
- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`

## getMfa

**Description:**

Get non-deleted user MFA, or quietly delete if invalid.

**Parameters:**

- `$email` (string)

**Returns:**

- (array): Keys: `created_at`, `expires_at`, `value`

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`

## mfaIsValid

**Description:**

Is MFA valid?
Quietly deletes MFA if expired.

**Parameters:**

- `$email` (string)
- `$value` (string)

**Returns:**

- (bool)

## deleteMfa

**Description:**

Quietly delete user MFA, if existing.

**Parameters:**

- `$email` (string)

**Returns:**

- (bool)

## deleteExpiredMfas

**Description:**

Quietly delete all expired MFA's.

**Parameters:**

- (none)

**Returns:**

- (void)