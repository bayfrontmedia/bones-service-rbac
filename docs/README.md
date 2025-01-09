# RBAC service

The RBAC service allows for multi-tenant role-based access control (RBAC) with an application built using the [Bones](https://github.com/bayfrontmedia/bones) framework.

## Exceptions

All exceptions originating from models throw [ORM service exceptions](https://github.com/bayfrontmedia/bones-service-orm).
All authentication exceptions extend `Bayfront\BonesService\Rbac\Exceptions\Authentication\UserAuthenticationException`,
so you can choose to catch exceptions as narrowly or broadly as you like.

## Overview

- Users, tenants and permissions are defined on a global level.
- Each tenant is owned by a single user.
- Users can own or belong to multiple tenants.
- Roles, role permissions and teams are defined per-tenant.
- Each role is granted a collection of permissions.
- Tenant owners automatically inherit all permissions.
- User permissions within a tenant (aside from the tenant owner) become the union of all the permissions granted to the roles to which they are assigned.
- Tenant users can belong to multiple roles and teams.
- Teams are used to horizontally partition tenant users, and have no bearing on their permissions.
- Users must be enabled and verified (if required) in order to authenticate. If a tenant is disabled, users will simply inherit no permissions for that tenant.

## Documentation

- [Initial setup](setup.md)
- [Events](events.md)
- [Filters](filters.md)
- [RbacService](rbacservice.md)
- [Tenant](tenant.md)
- [User](user.md)
- [Models](models/README.md)
- [Authentication](authentication/README.md)