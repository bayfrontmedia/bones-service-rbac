# [RBAC service](../README.md) > [Authentication](README.md) > TokenAuthenticator

The `Bayfront\BonesService\Rbac\Authenticators\TokenAuthenticator` is used to authenticate a user using an
access or refresh token.

## authenticate

**Description:**

Authenticate with [access or refresh token](../models/usertokens.md#createtoken).
Revokes tokens on errors as needed.

The `rbac.auth.success` [event](../events.md) is executed on success, and `rbac.auth.fail.token` on failure.

**Parameters:**

- `$token` (string): Token value
- `$type` (string): Any `TYPE_*` constant

**Returns:**

- [User](../user.md)

**Throws:**

- `Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidTokenException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException`