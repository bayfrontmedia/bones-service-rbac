# [RBAC service](../README.md) > [Authentication](README.md) > TotpAuthenticator

The `Bayfront\BonesService\Rbac\Authenticators\TotpAuthenticator` is used to authenticate a user using their
email address and a time-based one-time password (TOTP).

## authenticate

**Description:**

Authenticate with [user TOTP](../models/usermeta.md#createusertotp), quietly deleting if expired or when authenticated.

The `rbac.auth.success` [event](../events.md) is executed on success, and `rbac.auth.fail.totp` on failure.

**Parameters:**

- `$email` (string)
- `$value` (string)

**Returns:**

- [User](../user.md)

**Throws:**

- `Bayfront\BonesService\Rbac\Exceptions\Authentication\TotpDoesNotExistException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException`