# [RBAC service](../README.md) > Models

The following [resource models](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md) are added by this service.
They require an [RbacService](../rbacservice.md) instance to be passed to the constructor, and extend `Bayfront\BonesService\Rbac\Abstracts\RbacModel`, 
which extends [ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md).

All models use a UUIDv7 as the primary key.

- [Permissions](permissions.md)
- [TenantInvitations](tenantinvitations.md)
- [TenantMeta](tenantmeta.md)
- [TenantRolePermissions](tenantrolepermissions.md)
- [TenantRoles](tenantroles.md)
- [Tenants](tenants.md)
- [TenantTeams](tenantteams.md)
- [TenantUserMeta](tenantusermeta.md)
- [TenantUserRoles](tenantuserroles.md)
- [TenantUsers](tenantusers.md)
- [TenantUserTeams](tenantuserteams.md)
- [UserKeys](userkeys.md)
- [UserMeta](usermeta.md)
- [Users](users.md)