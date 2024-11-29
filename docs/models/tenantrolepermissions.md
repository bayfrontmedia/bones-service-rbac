# [RBAC service](../README.md) > [Models](README.md) > TenantRolePermissions

The `Bayfront\BonesService\Rbac\Models\TenantRolePermissions` model is used to assign permissions to tenant roles.

Allowed fields write:

- `role`: ([Tenant role ID](tenantroles.md)) Required, string
- `permission`: ([Permission ID](permissions.md)) Required, string

Unique fields:

- `role` + `permission`

Allowed fields read:

- `id`
- `role`
- `permission`
- `created_at`
- `updated_at`