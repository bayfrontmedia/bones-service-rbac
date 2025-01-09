# [RBAC service](../README.md) > Models

The following [resource models](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md) are added by this service.
They require an [RbacService](../rbacservice.md) instance to be passed to the constructor, and extend `Bayfront\BonesService\Rbac\Abstracts\RbacModel`, 
which extends [ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md).

All models use a UUIDv7 as the primary key.

- [PermissionsModel](permissions.md)
- [TenantInvitationsModel](tenantinvitations.md)
- [TenantMetaModel](tenantmeta.md)
- [TenantPermissionsModel](tenantpermissions.md)
- [TenantRolePermissionsModel](tenantrolepermissions.md)
- [TenantRolesModel](tenantroles.md)
- [TenantsModel](tenants.md)
- [TenantTeamsModel](tenantteams.md)
- [TenantUserMetaModel](tenantusermeta.md)
- [TenantUserRolesModel](tenantuserroles.md)
- [TenantUsersModel](tenantusers.md)
- [TenantUserTeamsModel](tenantuserteams.md)
- [UserKeysModel](userkeys.md)
- [UserMetaModel](usermeta.md)
- [UsersModel](users.md)