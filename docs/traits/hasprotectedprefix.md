# [RBAC service](../README.md) > [Traits](README.md) > HasProtectedPrefix

The `Bayfront\BonesService\Rbac\Traits\HasProtectedPrefix` trait is used by the following models:

- [TenantMetaModel](../models/tenantmeta.md)
- [TenantUserMetaModel](../models/tenantusermeta.md)
- [UserMetaModel](../models/usermeta.md)

This trait is used to filter meta using a protected prefix not returned by default.

The `protected_prefix` [config value](../setup.md#configuration) is used to restrict access to meta keys beginning with a protected prefix.
This can be overridden using the [withProtectedPrefix](#withprotectedprefix) and [onlyProtectedPrefix](#onlyprotectedprefix) functions.

Methods include:

- [withProtectedPrefix](#withprotectedprefix)
- [onlyProtectedPrefix](#onlyprotectedprefix)
- [getProtectedPrefix](#getprotectedprefix)
- [createTotp](#createtotp)
- [getTotp](#gettotp)
- [deleteTotp](#deletetotp)
- [deleteExpiredTotps](#deleteexpiredtotps)

## withProtectedPrefix

**Description:**

Filter next query to include protected prefix.

**Parameters:**

- (none)

**Returns:**

- `self`

## onlyProtectedPrefix

**Description:**

Filter next query to include only protected prefix.

**Parameters:**

- (none)

**Returns:**

- `self`

## getProtectedPrefix

**Description:**

Get protected prefix.

**Parameters:**

- (none)

**Returns:**

- (string)

## createTotp

**Description:**

Create TOTP (time-based one-time password) and save with hashed value, ensuring wait time has elapsed.
Value is hashed using [createHash](../rbacservice.md#createhash).
Returns TOTP with raw value.

**Parameters:**

- `$user_id` (string)
- `$meta_key` (string)
- `$wait` (int)
- `$duration` (int)
- `$length` (int)
- `$type` (string)

**Returns:**

- [Totp](../totp.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException` (Thrown when wait time has not elapsed)
- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException` (Thrown when user does not exist)
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getTotp

**Description:**

Get non-deleted, valid, unexpired TOTP.
Quietly deletes if invalid or expired.
Value can be verified using [hashMatches](../rbacservice.md#hashmatches).

**Parameters:**

- `$user_id` (string)
- `$meta_key` (string)

**Returns:**

- [Totp](../totp.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`

## deleteTotp

**Description:**

Quietly hard-delete TOTP, if existing.

**Parameters:**

- `$user_id` (string)
- `$meta_key` (string)

**Returns:**

- (bool)

## deleteExpiredTotps

**Description:**

Quietly hard-delete all expired TOTP's.

**Parameters:**

- `$meta_key` (string)

**Returns:**

- (void)