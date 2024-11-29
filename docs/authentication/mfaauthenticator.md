# [RBAC service](../README.md) > [Authentication](README.md) > MfaAuthenticator

The `Bayfront\BonesService\Rbac\Authenticators\MfaAuthenticator` is used to authenticate a user using their
email address and multifactor authentication code (MFA).

## authenticate

**Description:**

Authenticate with [MFA](../models/users.md#createmfa), quietly deleting if expired or when authenticated.

The `rbac.auth.success` [event](../events.md) is executed on success, and `rbac.auth.fail.mfa` on failure.

**Parameters:**

- `$email` (string)
- `$mfa_value` (string)

**Returns:**

- [User](../user.md)

**Throws:**

- `Bayfront\BonesService\Rbac\Exceptions\Authentication\MfaDoesNotExistException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException`