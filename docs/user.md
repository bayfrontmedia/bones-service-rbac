# [RBAC service](README.md) > User

The `Bayfront\BonesService\Rbac\User` class is used to represent a single user.
It is returned after any successful [authentication](authentication/README.md).

Its constructor requires an [RbacService](rbacservice.md) instance, and an [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) representing a single [user](models/users.md).

Methods include:

- [getResource](#getresource)
- [asObject](#asobject)
- [read](#read)
- [get](#get)
- [getId](#getid)
- [getEmail](#getemail)
- [getMeta](#getmeta)
- [isAdmin](#isadmin)
- [isEnabled](#isenabled)
- [isVerified](#isverified)
- [getTenantInvitations](#gettenantinvitations)
- [getTenants](#gettenants)
- [inTenant](#intenant)
- [inEnabledTenant](#inenabledtenant)
- [getOwnedTenants](#getownedtenants)
- [ownsTenant](#ownstenant)
- [getTenantUserIds](#gettenantuserids)
- [getTenantUserId](#gettenantuserid)
- [getRoles](#getroles)
- [hasAllRoles](#hasallroles)
- [hasAnyRoles](#hasanyroles)
- [hasRole](#hasrole)
- [getTeams](#getteams)
- [inAllTeams](#inallteams)
- [inAnyTeams](#inanyteams)
- [inTeam](#inteam)
- [getPermissions](#getpermissions)
- [hasAllPermissions](#hasallpermissions)
- [hasAnyPermissions](#hasanypermissions)
- [canDoAll](#candoall)
- [canDoAny](#candoany)
- [getUserMeta](#getusermeta)
- [getTenantUserMeta](#gettenantusermeta)

## getResource

**Description:**

Get user resource.

**Parameters:**

- (none)

**Returns:**

- [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md)

## asObject

**Description:**

Get entire user as an object.

**Parameters:**

- (none)

**Returns:**

- (object)

## read

**Description:**

Get entire user array.

**Parameters:**

- (none)

**Returns:**

- (array)

## get

**Description:**

Get single user field.

**Parameters:**

- `$field` (string)
- `$default = null` (mixed): Default value to return

**Returns:**

- (mixed)

## getId

**Description:**

Get user ID.

**Parameters:**

- (none)

**Returns:**

- (mixed)

## getEmail

**Description:**

Get email.

**Parameters:**

- (none)

**Returns:**

- (string)

## getMeta

**Description:**

Get meta key in dot notation, or default value if not existing.

**Parameters:**

- `$key` (string)
- `$default = null` (mixed)

**Returns:**

- (mixed)

## isAdmin

**Description:**

Is user an admin?

**Parameters:**

- (none)

**Returns:**

- (bool)

## isEnabled

**Description:**

Is user enabled?

**Parameters:**

- (none)

**Returns:**

- (bool)

## isVerified

**Description:**

Is user verified?

**Parameters:**

- (none)

**Returns:**

- (bool)

## getTenantInvitations

**Description:**

Get all user tenant invitations.

**Parameters:**

- (none)

**Returns:**

- (array)

## hasTenantInvitation

**Description:**

Does user have invitation ID?

**Parameters:**

- `$invitation_id` (string)

**Returns:**

- (bool)

## getTenants

**Description:**

Get all tenants user belongs to.

**Parameters:**

- (none)

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## inTenant

**Description:**

Is user in tenant?

**Parameters:**

- `$tenant_id` (string)

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## inEnabledTenant

**Description:**

Is user in enabled tenant?

**Parameters:**

- `$tenant_id` (string)

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getOwnedTenants

**Description:**

Get all tenants owned by user.

**Parameters:**

- (none)

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## ownsTenant

**Description:**

Does user own tenant?

**Parameters:**

- `$tenant_id` (string)

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getTenantUserIds

**Description:**

Get tenant user ID's.

**Parameters:**

- (none)

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getTenantUserId

**Description:**

Get tenant user ID for tenant, or `NULL` if not existing.

**Parameters:**

- `$tenant_id` (string)

**Returns:**

- (string|null)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getRoles

**Description:**

Get user roles for tenant.

**Parameters:**

- `$tenant_id` (string)

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## hasAllRoles

**Description:**

Does user have all roles for tenant?

**Parameters:**

- `$tenant_id` (string)
- `$role_ids` (array): Array of role ID's

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## hasAnyRoles

**Description:**

Does user have any roles for tenant?

**Parameters:**

- `$tenant_id` (string)
- `$role_ids` (array): Array of role ID's

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## hasRole

**Description:**

Does user have role?

**Parameters:**

- `$tenant_id` (string)
- `$role_id` (string)

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getTeams

**Description:**

Get user teams for tenant.

**Parameters:**

- `$tenant_id` (string)

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## inAllTeams

**Description:**

Is user in all teams for tenant?

**Parameters:**

- `$tenant_id` (string)
- `$team_ids` (array): Array of team ID's

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## inAnyTeams

**Description:**

Is user in any teams for tenant?

**Parameters:**

- `$tenant_id` (string)
- `$team_ids` (array): Array of team ID's

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## inTeam

**Description:**

Is user in team?

**Parameters:**

- `$tenant_id` (string)
- `$team_id` (string)

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getPermissions

**Description:**

Get all user permissions for tenant.
Admins and tenant owners automatically inherit all permissions.
If user or tenant is disabled, user will inherit no permissions.

**Parameters:**

- `$tenant_id` (string)

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## hasAllPermissions

**Description:**

Does user have all permissions for tenant?

**Parameters:**

- `$tenant_id` (string)
- `$permission_ids` (array): Array of permission ID's

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## hasAnyPermissions

**Description:**

Does user have any permissions for tenant?

**Parameters:**

- `$tenant_id` (string)
- `$permission_ids` (array): Array of permission ID's

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## canDoAll

**Description:**

Does user have all permission names for tenant?

**Parameters:**

- `$tenant_id` (string)
- `$permission_names` (array): Case-sensitive permission names

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## canDoAny

**Description:**

Does user have any permission names for tenant?

**Parameters:**

- `$tenant_id` (string)
- `$permission_names` (array): Case-sensitive permission names

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getUserMeta

**Description:**

Get user meta by meta key, or `NULL` if not existing.

**Parameters:**

- `$meta_key` (string)

**Returns:**

- (string|null)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`

## getTenantUserMeta

**Description:**

Get tenant user meta by meta key, or `NULL` if not existing.

**Parameters:**

- `$tenant_id` (string)
- `$meta_key` (string)

**Returns:**

- (string|null)

**Throws:**

- `Bayfront\BonesService\Orm\Exceptions\UnexpectedException`