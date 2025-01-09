# [RBAC service](README.md) > Events

The following [events](https://github.com/bayfrontmedia/bones/blob/master/docs/services/events.md) are added by this
service:

- `rbac.start`: Executes in the [RbacService](rbacservice.md) constructor as the first event available to this service. 
  The `RbacService` instance is passed as a parameter.
- `rbac.tenant.invitation.created`: Executed on creation of a [tenant invitation](models/tenantinvitations.md).
  An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance
  representing the created invitation is passed as a parameter.
- `rbac.tenant.invitation.accepted`: Executed when a [tenant invitation](models/tenantinvitations.md) is
  accepted.
  An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance
  representing the user and the tenant ID are passed as parameters.
- `rbac.user.created`: Executed when a [user is created](models/users.md). An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) representing the created user
  is passed as a parameter.
- `rbac.user.updated`: Executed when a [user is updated](models/users.md).
  An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance
  representing the updated resource,
  an `OrmResource` representing the pre-updated resource, and an array representing the updated fields are passed as
  parameters.
- `rbac.user.deleted`: Executed when a [user is deleted](models/users.md). An OrmResource representing the pre-deleted resource
  is passed as a parameter.
- `rbac.user.verified`: Executed when a [user is verified](models/users.md#verify). The user's email is passed as a
  parameter.
- `rbac.user.email.updated`: Executed when a [user's email](models/users.md) is updated.
  An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) representing the
  updated user is passed as a parameter.
- `rbac.user.password.updated`: Executed when a [user's password](models/users.md) is updated.
  An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) representing the
  updated user is passed as a parameter.
- `rbac.tenant.user.created`: Executed on creation of a [tenant user](models/tenantusers.md).
  An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance
  representing the created tenant user is passed as a parameter.
- `rbac.tenant.user.updated`: Executed when a [tenant user](models/tenantusers.md) is updated.
  An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance
  representing the updated resource,
  an `OrmResource` representing the pre-updated resource, and an array representing the updated fields are passed as
  parameters.
- `rbac.tenant.user.deleted`: Executed when a [tenant user](models/tenantusers.md) is deleted.
  An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance
  representing the pre-deleted resource
  is passed as a parameter.
- `rbac.tenant.created`: Executed when a [tenant is created](models/tenants.md). An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) representing the created tenant
  is passed as a parameter.
- `rbac.tenant.updated`: Executed when a [tenant is updated](models/tenants.md).
  An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance
  representing the updated resource,
  an `OrmResource` representing the pre-updated resource, and an array representing the updated fields are passed as
  parameters.
- `rbac.tenant.deleted`: Executed when a [tenant is deleted](models/tenants.md). An OrmResource representing the pre-deleted resource
  is passed as a parameter.
- `rbac.user.key.created`: Executed on creation of a [user key](models/userkeys.md).
  An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance
  representing the created user key is passed as a parameter.
- `rbac.user.key.updated`: Executed when a [user key](models/userkeys.md) is updated.
  An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) representing the
  updated resource,
  an `OrmResource` representing the pre-updated resource, and an array representing the updated fields are passed as
  parameters.
- `rbac.user.key.deleted`: Executed when a [user key](models/userkeys.md) is deleted.
  An [OrmResource](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormresource.md) instance
  representing the pre-deleted resource
  is passed as a parameter.