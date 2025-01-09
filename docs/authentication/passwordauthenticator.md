# [RBAC service](../README.md) > [Authentication](README.md) > PasswordAuthenticator

The `Bayfront\BonesService\Rbac\Authenticators\PasswordAuthenticator` is used to authenticate a user using their
email address and password.

## authenticate

**Description:**

Authenticate with [email and password](../models/users.md).

The `rbac.auth.success` [event](../events.md) is executed on success, and `rbac.auth.fail.password` on failure.

**Parameters:**

- `$email` (string)
- `$password` (string)

**Returns:**

- [User](../user.md)

**Throws:**

- `Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidPasswordException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException`