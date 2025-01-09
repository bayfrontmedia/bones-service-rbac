# [RBAC service](../README.md) > [Models](README.md) > TenantPermissionsModel

The `Bayfront\BonesService\Rbac\Models\TenantPermissionsModel` is used to manage tenant permissions.

Allowed fields write:

- `tenant`: ([Tenant ID](tenants.md)) Required, string
- `permission`: ([Permission ID](permissions.md)) Required, string

Unique fields:

- `tenant` + `permission`

Allowed fields read:

- `id`
- `tenant`
- `permission`
- `created_at`
- `updated_at`