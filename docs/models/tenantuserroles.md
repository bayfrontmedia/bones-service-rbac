# [RBAC service](../README.md) > [Models](README.md) > TenantUserRolesModel

The `Bayfront\BonesService\Rbac\Models\TenantUserRolesModel` is used to manage the roles of tenant users.

Allowed fields write:

- `tenant_user`: ([Tenant user ID](tenantusers.md)) Required, string
- `role`: ([Tenant role ID](tenantroles.md)) Required, string

Unique fields:

- `tenant_user` + `role`

Allowed fields read:

- `id`
- `tenant_user`
- `role`
- `created_at`
- `updated_at`