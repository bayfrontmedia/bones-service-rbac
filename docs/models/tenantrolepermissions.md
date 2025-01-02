# [RBAC service](../README.md) > [Models](README.md) > TenantRolePermissionsModel

The `Bayfront\BonesService\Rbac\Models\TenantRolePermissionsModel` is used to assign tenant permissions to tenant roles.

Allowed fields write:

- `role`: ([Tenant role ID](tenantroles.md)) Required, string
- `tenant_permission`: ([Tenant permission ID](tenantpermissions.md)) Required, string

Unique fields:

- `role` + `tenant_permission`

Allowed fields read:

- `id`
- `role`
- `tenant_permission`
- `created_at`
- `updated_at`