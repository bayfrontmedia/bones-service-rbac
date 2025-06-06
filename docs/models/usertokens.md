# [RBAC service](../README.md) > [Models](README.md) > UserTokensModel

The `Bayfront\BonesService\Rbac\Models\UserTokensModel` is used to manage user authentication tokens.

This model uses the [Castable](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/castable.md) and [HasNullableJsonField](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/hasnullablejsonfield.md) traits.

Allowed fields write:

- `user`: ([User ID](users.md)) Required, string
- `type`: Required, string, max length 255
- `expires`: Required, integer

Unique fields:

- (None)

Allowed fields read:

- `id`
- `user`
- `type`
- `expires`
- `ip`
- `meta`
- `created_at`

Model-specific constants used to define token types:

- `TOKEN_TYPE_ACCESS`
- `TOKEN_TYPE_REFRESH`

Model-specific methods include:


- [readByType](#readbytype)
- [createToken](#createtoken)
- [readToken](#readtoken)
- [deleteToken](#deletetoken)
- [deleteAllTokens](#deletealltokens)
- [deleteExpiredTokens](#deleteexpiredtokens)

## readByType

**Description:**

Find user token by user ID and type.

**Parameters:**

- `$user_id` (string)
- `$type` (string)

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\InvalidRequestException`
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
- `$ip` (string|null`): IP address which made the request
- `$meta` (string|array): Metadata

**Returns:**

- (string)

**Throws:**

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

Quietly delete token for user.

**Parameters:**

- `$user_id` (string)
- `$type` (string): `TOKEN_TYPE_*` constant

**Returns:**

- (bool)

## deleteAllTokens

**Description:**

Quietly delete all tokens for user.

**Parameters:**

- `$user_id` (string)

**Returns:**

- (bool)

## deleteExpiredTokens

**Description:**

Quietly delete all expired tokens.

**Parameters:**

- (none)

**Returns:**

- (void)