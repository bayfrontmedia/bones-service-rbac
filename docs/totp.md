# [RBAC service](README.md) > Totp

The `Bayfront\BonesService\Rbac\Totp` class is used to represent a TOTP (time-based one-time password).

Its constructor requires an array representing a [TOTP](rbacservice.md#createtotp).

Methods include:

- [getTotp](#gettotp)
- [getCreatedAt](#getcreatedat)
- [getExpiresAt](#getexpiresat)
- [getValue](#getvalue)

## getTotp

**Description:**

Get entire TOTP array.

**Parameters:**

- (none)

**Returns:**

- (array)

## getCreatedAt

**Description:**

Get `created_at` timestamp.

**Parameters:**

- (none)

**Returns:**

- (int)

## getExpiresAt

**Description:**

Get `expires_at` timestamp.

**Parameters:**

- (none)

**Returns:**

- (int)

## getValue

**Description:**

Get TOTP value.

**Parameters:**

- (none)

**Returns:**

- (string)