# [RBAC service](README.md) > Filters

The following [filters](https://github.com/bayfrontmedia/bones/blob/master/docs/services/filters.md) are added by this service:

- `rbac.token.payload`: Filters the [user token](models/usermeta.md#createtoken) payload array.
- `rbac.user.password`: Filters the [user password](models/users.md). This can be helpful to enforce password requirements at the app-level.
Extreme care should be taken to ensure the password is never leaked or stored.