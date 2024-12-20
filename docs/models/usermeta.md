# [RBAC service](../README.md) > [Models](README.md) > UserMetaModel

The `Bayfront\BonesService\Rbac\Models\UserMetaModel` is used to manage user metadata.

This model uses the [HasProtectedPrefix](../traits/hasprotectedprefix.md) and [SoftDeletes](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/softdeletes.md) traits.

Due to the way the field is stored in the database, meta values will always be returned as a string.
By JSON-encoding and decoding the value, other data types can be preserved.

Allowed fields write:

- `user`: ([User ID](users.md)) Required, string
- `meta_key`: Required, string, max length 255
- `meta_value`: Required, max length 4000000000

Unique fields:

- `user` + `meta_key`

Allowed fields read:

- `id`
- `user`
- `meta_key`
- `meta_value`
- `created_at`
- `updated_at`

Model-specific properties (string) used to return protected meta keys for TOTP's:

- `$totp_meta_key_password`
- `$totp_meta_key_tfa`
- `$totp_meta_key_verification`

Model-specific methods include:


- [findByKey](#findbykey)
- [createToken](#createtoken)
- [readToken](#readtoken)
- [deleteToken](#deletetoken)
- [deleteAllTokens](#deletealltokens)
- [deleteExpiredTokens](#deleteexpiredtokens)

## findByKey

**Description:**

Find user meta by user ID and meta key value.

Can be used with the `SoftDeletes` trait trashed filters.

**Parameters:**

- `$user_id` (string)
- `$meta_key` (string)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## createToken

**Description:**

Create token for user.

When the `user.token.revocable` [config value](../setup.md#configuration) is `true`, access tokens are stored in the database and require a database
query to authenticate.

The validity duration is based on the `user.token.access_duration` and `user.token.refresh.duration` config values.

The token payload array can be modified using the `rbac.token.payload` [filter](../filters.md).

**Parameters:**

- `$user_id` (string)
- `$type` (string): `TOKEN_TYPE_*` constant

**Returns:**

- (string)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## readToken

**Description:**

Read token payload.

NOTE: This does not perform any validation.

**Parameters:**

- `$token` (string)

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## deleteToken

**Description:**

Quietly hard-delete token for user.

**Parameters:**

- `$user_id` (string)
- `$type` (string): `TOKEN_TYPE_*` constant

**Returns:**

- (bool)

## deleteAllTokens

**Description:**

Quietly hard-delete access and refresh tokens for user.

**Parameters:**

- `$user_id` (string)

**Returns:**

- (bool)

## deleteExpiredTokens

**Description:**

Quietly delete all expired access and refresh tokens.

**Parameters:**

- (none)

**Returns:**

- (void)

## createPasswordRequest

**Description:**

Create password request, verifying TOTP wait time has elapsed.
Value is hashed using [createHash](../rbacservice.md#createhash)

**Parameters:**

- `$user_id` (string)
- `$length` (int)
- `$type` (string): Any [RbacService](../rbacservice.md) `TOTP_TYPE_*` constant

**Returns:**

- [Totp](../totp.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getPasswordRequest

**Description:**

Get non-deleted password request, or quietly delete if invalid or expired.
Value can be verified using [hashMatches](../rbacservice.md#hashmatches).

**Parameters:**

- `$user_id` (string)

**Returns:**

- [Totp](../totp.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`

## deletePasswordRequest

**Description:**

Quietly hard-delete password request, if existing.

**Parameters:**

- `$user_id` (string)

**Returns:**

- (bool)

## deleteExpiredPasswordRequests

**Description:**

Quietly hard-delete all expired password requests.

**Parameters:**

- (none)

**Returns:**

- (void)

## createUserTotp

**Description:**

Create user TOTP, verifying TOTP wait time has elapsed.
Value is hashed using [createHash](../rbacservice.md#createhash)

**Parameters:**

- `$user_id` (string)
- `$length` (int)
- `$type` (string): Any [RbacService](../rbacservice.md) `TOTP_TYPE_*` constant

**Returns:**

- [Totp](../totp.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getUserTotp

**Description:**

Get non-deleted user TOTP, or quietly delete if invalid or expired.
Value can be verified using [hashMatches](../rbacservice.md#hashmatches).

**Parameters:**

- `$user_id` (string)

**Returns:**

- [Totp](../totp.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`

## deleteUserTotp

**Description:**

Quietly hard-delete user TOTP, if existing.

**Parameters:**

- `$user_id` (string)

**Returns:**

- (bool)

## deleteExpiredUserTotps

**Description:**

Quietly hard-delete all expired user TOTP's.

**Parameters:**

- (none)

**Returns:**

- (void)

## createUserVerification

**Description:**

Create user verification, verifying TOTP wait time has elapsed.
Value is hashed using [createHash](../rbacservice.md#createhash)

**Parameters:**

- `$user_id` (string)
- `$length` (int)
- `$type` (string): Any [RbacService](../rbacservice.md) `TOTP_TYPE_*` constant

**Returns:**

- [Totp](../totp.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getUserVerification

**Description:**

Get non-deleted user verification, or quietly delete if invalid or expired.
Value can be verified using [hashMatches](../rbacservice.md#hashmatches).

**Parameters:**

- `$user_id` (string)

**Returns:**

- [Totp](../totp.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`

## deleteUserVerification

**Description:**

Quietly hard-delete user verification, if existing.

**Parameters:**

- `$user_id` (string)

**Returns:**

- (bool)

## deleteExpiredUserVerifications

**Description:**

Quietly hard-delete all expired user verification TOTP's.

**Parameters:**

- (none)

**Returns:**

- (void)