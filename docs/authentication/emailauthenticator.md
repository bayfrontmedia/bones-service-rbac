# [RBAC service](../README.md) > [Authentication](README.md) > EmailAuthenticator

The `Bayfront\BonesService\Rbac\Authenticators\EmailAuthenticator` is used to authenticate a user using their
email address.

## authenticate

**Description:**

Authenticate with [email](../models/users.md).

The `rbac.auth.fail.email` [event](../events.md) is executed on failure.

NOTE:
This should be used in conjunction with another authentication method,
such as a [TOTP](totpauthenticator.md).
Because of this, the `rbac.auth.success` [event](../events.md) is never executed.

**Parameters:**

- `$email` (string)
- `$check_verified = true` (bool): Check if user is verified when require verification is enabled

**Returns:**

- [User](../user.md)

**Throws:**

- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException`