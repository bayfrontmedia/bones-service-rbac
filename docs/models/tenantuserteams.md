# [RBAC service](../README.md) > [Models](README.md) > TenantUserTeamsModel

The `Bayfront\BonesService\Rbac\Models\TenantUserTeamsModel` is used to manage the teams of tenant users.

Allowed fields write:

- `tenant_user`: ([Tenant user ID](tenantusers.md)) Required, string
- `team`: ([Tenant team ID](tenantteams.md)) Required, string

Unique fields:

- `tenant_user` + `team`

Allowed fields read:

- `id`
- `tenant_user`
- `team`
- `created_at`
- `updated_at`