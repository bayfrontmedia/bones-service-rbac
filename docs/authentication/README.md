# [RBAC service](../README.md) > Authentication

This library handles user authentication and authorization for specific permissions within a tenant.
How these are implemented is handled on the individual application level.

For example, a user can authenticate with their email and password,
but how it is persisted between requests (ie: sessions, JWT, etc.) is entirely implemented by the application.

Users must be enabled in order to authenticate.
If a user belongs to a disabled [tenant](../models/tenants.md), the user will still be able to authenticate, 
but will simply inherit no permissions for that tenant.

In addition, if new user verification is required via the `user.verification.require` [config value](../setup.md#configuration),
the `verified_at` field of the [users](../models/users.md#verify) table must be defined in order for the user to authenticate.
Implementation of how a user is verified is handled at the app-level.
Typically, a complex TOTP can be created and emailed to the user. Once the TOTP is confirmed, the user can then be verified
and the TOTP can be deleted.

User permissions can be calculated using the [User](../user.md) class.

## Authentication methods

- [EmailAuthenticator](emailauthenticator.md)
- [PasswordAuthenticator](passwordauthenticator.md)
- [TokenAuthenticator](tokenauthenticator.md)
- [TotpAuthenticator](totpauthenticator.md)
- [UserKeyAuthenticator](userkeyauthenticator.md)