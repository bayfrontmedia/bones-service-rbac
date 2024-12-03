# [RBAC service](README.md) > Events

The following [events](https://github.com/bayfrontmedia/bones/blob/master/docs/services/events.md) are added by this service:

- `rbac.auth.success`: Executed on successful user [authentication](authentication/README.md) for all authenticators except for [EmailAuthenticator](authentication/emailauthenticator.md).
A [User](user.md) instance is passed as a parameter.
- `rbac.auth.fail.email`: Executed on unsuccessful [email authentication](authentication/emailauthenticator.md) attempt. The email address is passed as a parameter.
- `rbac.auth.fail.mfa`: Executed on unsuccessful [MFA authentication](authentication/mfaauthenticator.md) attempt. The email address is passed as a parameter.
- `rbac.auth.fail.password`: Executed on unsuccessful [password authentication](authentication/passwordauthenticator.md) attempt. The email address is passed as a parameter.
- `rbac.auth.fail.token`: Executed on unsuccessful [token authentication](authentication/tokenauthenticator.md) attempt. No parameters are passed.
- `rbac.auth.fail.key`: Executed on unsuccessful [user key authentication](authentication/userkeyauthenticator.md) attempt. No parameters are passed.
- `rbac.tenant.invitation.created`: Executed on creation of a [tenant invitation](models/tenantinvitations.md). 
An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance representing the created invitation is passed as a parameter. 
- `rbac.tenant.invitation.accepted`: Executed when a [tenant invitation](models/tenantinvitations.md#accept) is accepted.
An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance representing the user and the tenant ID are passed as parameters.
- `rbac.tenant.user.created`: Executed on creation of a [tenant user](models/tenantusers.md).
An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance representing the created tenant user is passed as a parameter.
- `rbac.tenant.user.updated`: Executed when a [tenant user](models/tenantusers.md) is updated.
An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance representing the updated resource,
an `OrmResource` representing the pre-updated resource, and an array representing the updated fields are passed as parameters.
- `rbac.tenant.user.deleted`: Executed when a [tenant user](models/tenantusers.md) is deleted.
An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance representing the pre-deleted resource
is passed as a parameter.
- `rbac.tenant.owner.updated`: Executed when the [tenant owner](models/tenants.md) is updated. 
An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance representing the updated resource, 
an `OrmResource` representing the pre-updated resource, and an array representing the updated fields are passed as parameters.
- `rbac.user.key.created`: Executed on creation of a [user key](models/userkeys.md).
An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance representing the created user key is passed as a parameter.
- `rbac.user.key.updated`: Executed when a [user key](models/userkeys.md) is updated.
An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) representing the updated resource,
an `OrmResource` representing the pre-updated resource, and an array representing the updated fields are passed as parameters.
- `rbac.user.key.deleted`: Executed when a [user key](models/userkeys.md) is deleted.
An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance representing the pre-deleted resource
is passed as a parameter.
- `rbac.user.password.request`: Executed on creation of a [password request](models/usermeta.md#createpasswordrequest).
The user ID and created password request array are passed as parameters.
- `rbac.user.password.updated`: Executed when a [user's password](models/users.md) is updated.
An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) representing the updated user is passed as a parameter.
- `rbac.user.verified`: Executed when a [user is verified](models/users.md#verify). The user's email is passed as a parameter.
- `rbac.user.mfa.created`: Executed when a [user MFA](models/users.md#createmfa) is created.
The user's email and an array with the keys `created_at`, `expires_at` and `value` are passed as parameters. 
User MFA verified events can take place at the app-level.