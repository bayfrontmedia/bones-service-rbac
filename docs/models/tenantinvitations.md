# [RBAC service](../README.md) > [Models](README.md) > TenantInvitationsModel

The `Bayfront\BonesService\Rbac\Models\TenantInvitationsModel` is used to manage tenant invitations.

This model uses the [Prunable](https://github.com/bayfrontmedia/bones-service-orm/blob/master/src/Traits/Prunable.php) 
and [SoftDeletes](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/softdeletes.md) traits.

Resources are prunable using the `expires_at` field.

The `expires_at` field will be defined on creation using the `invitation_duration` [config value](../setup.md#configuration).
Upon successful creation, the `rbac.tenant.invitation.created` [event](../events.md) will be executed.

Allowed fields write:

- `email`: Required, email, max length 255
- `tenant`: ([Tenant ID](tenants.md)) Required, string
- `role`: ([Tenant role ID](tenantroles.md)) Required, string

Unique fields:

- `email` + `tenant`

Allowed fields read:

- `id`
- `email`
- `tenant`
- `role`
- `expires_at`
- `created_at`
- `updated_at`

Model-specific methods include:

- [findByEmail](#findbyemail)
- [acceptFromId](#acceptfromid)
- [acceptFromEmail](#acceptfromemail)

## findByEmail

**Description:**

Find tenant invitation by email and tenant ID.

Can be used with the `SoftDeletes` trait trashed filters.

**Parameters:**

- `$email` (string)
- `$tenant_id` (string)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## acceptFromId

**Description:**

Accept tenant invitation using invitation ID.

Adds non-deleted user to tenant with invited role and hard-deletes invitation.
The `rbac.tenant.invitation.accepted` [event](../events.md) is executed on success.

**Parameters:**

- `$invitation_id` (string)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\InvalidFieldException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## acceptFromEmail

**Description:**

Accept tenant invitation using email and tenant ID.

Adds non-deleted user to tenant with invited role and hard-deletes invitation.
The `rbac.tenant.invitation.accepted` [event](../events.md) is executed on success.

**Parameters:**

- `$email` (string)
- `$tenant_id` (string)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\DoesNotExistException`
- `Bayfront\BonesService\Orm\Exceptions\InvalidFieldException`
- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`