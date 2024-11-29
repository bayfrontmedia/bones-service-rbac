# [RBAC service](../README.md) > [Authentication](README.md) > UserKeyAuthenticator

The `Bayfront\BonesService\Rbac\Authenticators\UserKeyAuthenticator` is used to authenticate a user using an
API key.

## authenticate

**Description:**

Authenticate with [user key](../models/userkeys.md).

The `rbac.auth.success` [event](../events.md) is executed on success, and `rbac.auth.fail.key` on failure.

**Parameters:**

- `$user_key` (string): Token value
- `$ip = ''` (string): Client IP address
- `$domain = ''` (string): Client referring domain

**Returns:**

- [User](../user.md)

**Throws:**

- `Bayfront\BonesService\Rbac\Exceptions\Authentication\ExpiredUserKeyException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidDomainException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidIpException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidUserKeyException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException`
- `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException`