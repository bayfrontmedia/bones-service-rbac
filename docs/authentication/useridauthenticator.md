# [RBAC service](../README.md) > [Authentication](README.md) > UserIdAuthenticator

The `Bayfront\BonesService\Rbac\Authenticators\UserIdAuthenticator` is used to authenticate a user using their ID.

NOTE: Authentication is not secure with this method alone.

## authenticate

**Description:**

Authenticate with [user id](../models/users.md).

**Parameters:**

- `$user_id` (mixed)
- `$check_verified = true` (bool): Check if user is verified when require verification is enabled

**Returns:**

- [User](../user.md)

**Throws:**

- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException`