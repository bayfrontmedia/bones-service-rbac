# [RBAC service](../README.md) > [Models](README.md) > UserMetaModel

The `Bayfront\BonesService\Rbac\Models\UserMetaModel` is used to manage user metadata.

This model uses the [HasProtectedPrefix](../traits/hasprotectedprefix.md) trait.

Due to the way the field is stored in the database, meta values will always be returned as a string.
By JSON-encoding and decoding the value, other data types can be preserved.

Allowed fields write:

- `user`: ([User ID](users.md)) Required, string
- `meta_key`: Required, string, max length 255
- `meta_value`: Required, max length 4000000000

Unique fields:

- `user` + `meta_key`

Allowed fields read:

- `id`
- `user`
- `meta_key`
- `meta_value`
- `created_at`
- `updated_at`

Model-specific properties (string) used to return protected meta keys for TOTP's:

- `$totp_meta_key_password`
- `$totp_meta_key_tfa`
- `$totp_meta_key_verification`

Model-specific methods include:


- [findByKey](#findbykey)

## findByKey

**Description:**

Find user meta by user ID and meta key value.

**Parameters:**

- `$user_id` (string)
- `$meta_key` (string)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`