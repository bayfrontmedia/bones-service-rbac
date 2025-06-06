# [RBAC service](../README.md) > [Authentication](README.md) > EmailAuthenticator

The `Bayfront\BonesService\Rbac\Authenticators\EmailAuthenticator` is used to authenticate a user using their
email address.

NOTE: Authentication is not secure with this method alone.
To be secure, this authentication method should be used in conjunction with another authentication method, such as a TOTP.

## authenticate

**Description:**

Authenticate with [email](../models/users.md).

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