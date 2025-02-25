<?php

namespace Bayfront\BonesService\Rbac;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\OrmServiceException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\BonesService\Rbac\Models\PermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantInvitationsModel;
use Bayfront\BonesService\Rbac\Models\TenantPermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantRolePermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantUserMetaModel;
use Bayfront\BonesService\Rbac\Models\TenantUserRolesModel;
use Bayfront\BonesService\Rbac\Models\TenantUsersModel;
use Bayfront\BonesService\Rbac\Models\TenantUserTeamsModel;
use Bayfront\BonesService\Rbac\Models\UserMetaModel;

/**
 * Read-only user.
 */
class User
{

    public RbacService $rbacService;
    public OrmResource $ormResource;
    private array $user;

    /**
     * @param RbacService $rbacService
     * @param OrmResource $ormResource (UsersModel resource)
     */
    public function __construct(RbacService $rbacService, OrmResource $ormResource)
    {
        $this->rbacService = $rbacService;
        $this->ormResource = $ormResource;
        $this->user = $ormResource->read();
    }

    /*
     * |--------------------------------------------------------------------------
     * | User
     * |--------------------------------------------------------------------------
     */

    /**
     * Get user resource.
     *
     * @return OrmResource
     */
    public function getResource(): OrmResource
    {
        return $this->ormResource;
    }

    /**
     * Get entire user as an object.
     *
     * @return object
     */
    public function asObject(): object
    {
        return $this->ormResource->asObject();
    }

    /**
     * Get entire user array.
     *
     * @return array
     */
    public function read(): array
    {
        return $this->user;
    }

    /**
     * Get single user field.
     *
     * @param string $field
     * @param mixed $default (Default value to return)
     * @return mixed
     */
    public function get(string $field, mixed $default = null): mixed
    {
        return Arr::get($this->user, $field, $default);
    }

    /**
     * Get user ID.
     *
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->ormResource->getPrimaryKey();
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return Arr::get($this->user, 'email', '');
    }

    /**
     * Get meta key in dot notation, or default value if not existing.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return Arr::get(Arr::get($this->user, 'meta', []), $key, $default);
    }

    /**
     * Is user an admin?
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return Arr::get($this->user, 'admin', false);
    }

    /**
     * Is user enabled?
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return Arr::get($this->user, 'enabled', false);
    }

    /***
     * Is user verified?
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return Arr::get($this->user, 'verified_at') !== null;
    }

    /*
     * |--------------------------------------------------------------------------
     * | Tenant invitations
     * |--------------------------------------------------------------------------
     */

    private ?array $invitations = null;

    /**
     * @return void
     * @throws InvalidRequestException
     * @throws UnexpectedException
     */
    private function defineInvitations(): void
    {

        if (is_array($this->invitations)) {
            return;
        }

        $tenantInvitationsModel = new TenantInvitationsModel($this->rbacService);

        $invitations = $tenantInvitationsModel->list(new QueryParser([
            'fields' => [
                '*.*'
            ],
            'filter' => [
                [
                    'email' => [
                        'eq' => $this->getEmail()
                    ]
                ]
            ]
        ]), true);

        $this->invitations = $invitations->list();

    }

    /**
     * Get all user tenant invitations.
     *
     * @return array
     * @throws UnexpectedException
     */
    public function getTenantInvitations(): array
    {

        try {
            $this->defineInvitations();
        } catch (OrmServiceException) {
            throw new UnexpectedException('Unable to retrieve user tenant invitations');
        }

        return $this->invitations;

    }

    /**
     * Does user have invitation ID?
     *
     * @param string $invitation_id
     * @return bool
     * @throws UnexpectedException
     */
    public function hasTenantInvitation(string $invitation_id): bool
    {
        return in_array($invitation_id, Arr::pluck($this->getTenantInvitations(), 'id'));
    }

    /*
     * |--------------------------------------------------------------------------
     * | Tenants
     * |--------------------------------------------------------------------------
     */

    private ?array $tenants = null;

    private array $tenant_users = []; // Key = tenant, value = tenant user ID

    /**
     * @return void
     * @throws UnexpectedException
     * @throws InvalidRequestException
     */
    private function defineTenants(): void
    {

        if (is_array($this->tenants)) {
            return;
        }

        $tenantUsersModel = new TenantUsersModel($this->rbacService);

        $tenants = $tenantUsersModel->list(new QueryParser([
            'fields' => [
                'id',
                'tenant.*'
            ],
            'filter' => [
                [
                    'user' => [
                        'eq' => $this->getId()
                    ]
                ]
            ]
        ]), true);

        $user_tenants = $tenants->list();

        $this->tenants = Arr::pluck($user_tenants, 'tenant');

        foreach ($user_tenants as $ut) {
            $this->tenant_users[] = [
                'tenant' => Arr::get($ut, 'tenant.id', ''),
                'tenant_user' => $ut['id']
            ];
        }

    }

    /**
     * Get all tenants user belongs to.
     *
     * @return array
     * @throws UnexpectedException
     */
    public function getTenants(): array
    {

        try {
            $this->defineTenants();
        } catch (OrmServiceException) {
            throw new UnexpectedException('Unable to retrieve user tenants');
        }

        return $this->tenants;
    }

    /**
     * Is user in tenant?
     *
     * @param string $tenant_id
     * @return bool
     * @throws UnexpectedException
     */
    public function inTenant(string $tenant_id): bool
    {
        return in_array($tenant_id, Arr::pluck($this->getTenants(), 'id'));
    }

    /**
     * Is user in enabled tenant?
     *
     * @param string $tenant_id
     * @return bool
     * @throws UnexpectedException
     */
    public function inEnabledTenant(string $tenant_id): bool
    {

        foreach ($this->getTenants() as $tenant) {

            if (Arr::get($tenant, 'id') == $tenant_id) {

                return $this->tenantIsEnabled($tenant_id);

            }

        }

        return false;

    }

    /**
     * Get all tenants owned by user.
     *
     * @return array
     * @throws UnexpectedException
     */
    public function getOwnedTenants(): array
    {

        $tenants = $this->getTenants();

        foreach ($tenants as $k => $tenant) {

            if (Arr::get($tenant, 'owner') !== $this->getId()) {
                unset($tenants[$k]);
            }

        }

        return array_values($tenants); // Reset keys

    }

    /**
     * Does user own tenant?
     *
     * @param string $tenant_id
     * @return bool
     * @throws UnexpectedException
     */
    public function ownsTenant(string $tenant_id): bool
    {
        return in_array($tenant_id, Arr::pluck($this->getOwnedTenants(), 'id'));
    }

    /**
     * Get tenant user ID's.
     *
     * @return array
     * @throws UnexpectedException
     */
    public function getTenantUserIds(): array
    {

        try {
            $this->defineTenants();
        } catch (OrmServiceException) {
            throw new UnexpectedException('Unable to retrieve tenant user IDs');
        }

        return $this->tenant_users;
    }

    /**
     * Get tenant user ID for tenant, or NULL if not existing.
     *
     * @param string $tenant_id
     * @return string|null
     * @throws UnexpectedException
     */
    public function getTenantUserId(string $tenant_id): ?string
    {

        foreach ($this->getTenantUserIds() as $tu) {

            if (Arr::get($tu, 'tenant') == $tenant_id) {
                return Arr::get($tu, 'tenant_user');
            }
        }

        return null;

    }

    /*
     * |--------------------------------------------------------------------------
     * | Tenant roles
     * |--------------------------------------------------------------------------
     */

    private array $roles = [];

    /**
     * @param string $tenant_id
     * @return void
     * @throws InvalidRequestException
     * @throws UnexpectedException
     */
    private function defineRoles(string $tenant_id): void
    {

        if (isset($this->roles[$tenant_id])) {
            return;
        }

        $tenant_user_id = $this->getTenantUserId($tenant_id);

        if ($tenant_user_id === null) { // Does not belong to tenant
            $this->roles[$tenant_id] = [];
            return;
        }

        $tenantUserRolesModel = new TenantUserRolesModel($this->rbacService);

        $roles = $tenantUserRolesModel->list(new QueryParser([
            'fields' => [
                'role.*'
            ],
            'filter' => [
                [
                    'tenant_user' => [
                        'eq' => $tenant_user_id
                    ]
                ]
            ]
        ]), true);

        $this->roles[$tenant_id] = Arr::pluck($roles->list(), 'role');

    }

    /**
     * Get user roles for tenant.
     *
     * @param string $tenant_id
     * @return array
     * @throws UnexpectedException
     */
    public function getRoles(string $tenant_id): array
    {

        try {
            $this->defineRoles($tenant_id);
        } catch (OrmServiceException) {
            throw new UnexpectedException('Unable to retrieve user roles');
        }

        return Arr::get($this->roles, $tenant_id, []);
    }

    /**
     * Does user have all roles for tenant?
     *
     * @param string $tenant_id
     * @param array $role_ids (Array of role ID's)
     * @return bool
     * @throws UnexpectedException
     */
    public function hasAllRoles(string $tenant_id, array $role_ids): bool
    {
        return Arr::hasAllValues(Arr::pluck($this->getRoles($tenant_id), 'id'), $role_ids);
    }

    /**
     * Does user have any roles for tenant?
     *
     * @param string $tenant_id
     * @param array $role_ids (Array of role ID's)
     * @return bool
     * @throws UnexpectedException
     */
    public function hasAnyRoles(string $tenant_id, array $role_ids): bool
    {
        return Arr::hasAnyValues(Arr::pluck($this->getRoles($tenant_id), 'id'), $role_ids);
    }

    /**
     * Does user have role?
     *
     * @param string $tenant_id
     * @param string $role_id
     * @return bool
     * @throws UnexpectedException
     */
    public function hasRole(string $tenant_id, string $role_id): bool
    {
        return in_array($role_id, Arr::pluck($this->getRoles($tenant_id), 'id'));
    }

    /*
     * |--------------------------------------------------------------------------
     * | Tenant teams
     * |--------------------------------------------------------------------------
     */

    private array $teams = [];

    /**
     * @param string $tenant_id
     * @return void
     * @throws InvalidRequestException
     * @throws UnexpectedException
     */
    private function defineTeams(string $tenant_id): void
    {

        if (isset($this->teams[$tenant_id])) {
            return;
        }

        $tenant_user_id = $this->getTenantUserId($tenant_id);

        if ($tenant_user_id === null) { // Does not belong to tenant
            $this->teams[$tenant_id] = [];
            return;
        }

        $tenantUserTeamsModel = new TenantUserTeamsModel($this->rbacService);

        $teams = $tenantUserTeamsModel->list(new QueryParser([
            'fields' => [
                'team.*'
            ],
            'filter' => [
                [
                    'tenant_user' => [
                        'eq' => $tenant_user_id
                    ]
                ]
            ]
        ]), true);

        $this->teams[$tenant_id] = Arr::pluck($teams->list(), 'team');

    }

    /**
     * Get user teams for tenant.
     *
     * @param string $tenant_id
     * @return array
     * @throws UnexpectedException
     */
    public function getTeams(string $tenant_id): array
    {

        try {
            $this->defineTeams($tenant_id);
        } catch (OrmServiceException) {
            throw new UnexpectedException('Unable to retrieve user teams');
        }

        return Arr::get($this->teams, $tenant_id, []);
    }

    /**
     * Is user in all teams for tenant?
     *
     * @param string $tenant_id
     * @param array $team_ids (Array of team ID's)
     * @return bool
     * @throws UnexpectedException
     */
    public function inAllTeams(string $tenant_id, array $team_ids): bool
    {
        return Arr::hasAllValues(Arr::pluck($this->getTeams($tenant_id), 'id'), $team_ids);
    }

    /**
     * Is user in any teams for tenant?
     *
     * @param string $tenant_id
     * @param array $team_ids (Array of team ID's)
     * @return bool
     * @throws UnexpectedException
     */
    public function inAnyTeams(string $tenant_id, array $team_ids): bool
    {
        return Arr::hasAnyValues(Arr::pluck($this->getTeams($tenant_id), 'id'), $team_ids);
    }

    /**
     * Is user in team?
     *
     * @param string $tenant_id
     * @param string $team_id
     * @return bool
     * @throws UnexpectedException
     */
    public function inTeam(string $tenant_id, string $team_id): bool
    {
        return in_array($team_id, Arr::pluck($this->getTeams($tenant_id), 'id'));
    }

    /*
     * |--------------------------------------------------------------------------
     * | User permissions
     * |--------------------------------------------------------------------------
     */

    private array $permissions = [];

    /**
     * Is tenant enabled?
     *
     * @param string $tenant_id
     * @return bool
     * @throws UnexpectedException
     */
    private function tenantIsEnabled(string $tenant_id): bool
    {

        foreach ($this->getTenants() as $tenant) {

            if (Arr::get($tenant, 'id') == $tenant_id) {
                if (Arr::get($tenant, 'enabled') === true) {
                    return true;
                }

                return false;

            }

        }

        return false;

    }

    /**
     * @param string $tenant_id
     * @return void
     * @throws InvalidRequestException
     * @throws UnexpectedException
     */
    private function definePermissions(string $tenant_id): void
    {

        if (isset($this->permissions[$tenant_id])) {
            return;
        }

        if (!$this->isEnabled()) {
            $this->permissions[$tenant_id] = [];
            return;
        }

        if (!$this->isAdmin() && !$this->tenantIsEnabled($tenant_id)) {
            $this->permissions[$tenant_id] = [];
            return;
        }

        if ($this->isAdmin()) {

            $permissionsModel = new PermissionsModel($this->rbacService);

            $permissions = $permissionsModel->list(new QueryParser([

            ]), true);

            $this->permissions[$tenant_id] = $permissions->list();

        } else if ($this->ownsTenant($tenant_id)) {

            $tenantPermissionsModel = new TenantPermissionsModel($this->rbacService);

            $permissions = $tenantPermissionsModel->list(new QueryParser([
                'fields' => [
                    'permission.*'
                ],
                'filter' => [
                    [
                        'tenant' => [
                            'eq' => $tenant_id
                        ]
                    ]
                ]
            ]), true);

            $this->permissions[$tenant_id] = Arr::pluck($permissions->list(), 'permission');

        } else {

            $tenantRolePermissionsModel = new TenantRolePermissionsModel($this->rbacService);

            $permissions = $tenantRolePermissionsModel->list(new QueryParser([
                'fields' => [
                    'tenant_permission.*.*'
                ],
                'filter' => [
                    [
                        'role' => [
                            'in' => implode(',', Arr::pluck($this->getRoles($tenant_id), 'id'))
                        ]
                    ]
                ]
            ]), true);

            $this->permissions[$tenant_id] = Arr::pluck($permissions->list(), 'tenant_permission.permission');

        }

    }

    /**
     * Get all user permissions for tenant.
     * Admins inherit all existing permissions.
     * Tenant owners inherit all tenant permissions.
     * If user or tenant is disabled, user will inherit no permissions.
     *
     * @param string $tenant_id
     * @return array
     * @throws UnexpectedException
     */
    public function getPermissions(string $tenant_id): array
    {

        try {
            $this->definePermissions($tenant_id);
        } catch (OrmServiceException) {
            throw new UnexpectedException('Unable to retrieve user permissions');
        }

        return Arr::get($this->permissions, $tenant_id, []);
    }

    /**
     * Does user have all permissions for tenant?
     *
     * @param string $tenant_id
     * @param array $permission_ids (Array of permission ID's)
     * @return bool
     * @throws UnexpectedException
     */
    public function hasAllPermissions(string $tenant_id, array $permission_ids): bool
    {
        return Arr::hasAllValues(Arr::pluck($this->getPermissions($tenant_id), 'id'), $permission_ids);
    }

    /**
     * Does user have any permissions for tenant?
     *
     * @param string $tenant_id
     * @param array $permission_ids (Array of permission ID's)
     * @return bool
     * @throws UnexpectedException
     */
    public function hasAnyPermissions(string $tenant_id, array $permission_ids): bool
    {
        return Arr::hasAnyValues(Arr::pluck($this->getPermissions($tenant_id), 'id'), $permission_ids);
    }

    /**
     * Does user have all permission names for tenant?
     *
     * @param string $tenant_id
     * @param array $permission_names (Case-sensitive permission names)
     * @return bool
     * @throws UnexpectedException
     */
    public function canDoAll(string $tenant_id, array $permission_names): bool
    {
        return Arr::hasAllValues(Arr::pluck($this->getPermissions($tenant_id), 'name'), $permission_names);
    }

    /**
     * Does user have any permission names for tenant?
     *
     * @param string $tenant_id
     * @param array $permission_names (Case-sensitive permission names)
     * @return bool
     * @throws UnexpectedException
     */
    public function canDoAny(string $tenant_id, array $permission_names): bool
    {
        return Arr::hasAnyValues(Arr::pluck($this->getPermissions($tenant_id), 'name'), $permission_names);
    }

    /*
     * |--------------------------------------------------------------------------
     * | User meta
     * |--------------------------------------------------------------------------
     */

    private array $user_meta = [];

    /**
     *
     * @param string $meta_key
     * @return void
     * @throws UnexpectedException
     */
    private function defineUserMeta(string $meta_key): void
    {

        if (isset($this->user_meta[$meta_key])) {
            return;
        }

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $meta = $userMetaModel->findByKey($this->getId(), $meta_key);
            $this->user_meta[$meta_key] = $meta->get('meta_value');
        } catch (DoesNotExistException) {
            $this->user_meta[$meta_key] = null;
        }

    }

    /**
     * Get user meta by meta key, or NULL if not existing.
     *
     * @param string $meta_key
     * @return string|null
     * @throws UnexpectedException
     */
    public function getUserMeta(string $meta_key): ?string
    {

        try {
            $this->defineUserMeta($meta_key);
        } catch (OrmServiceException) {
            throw new UnexpectedException('Unable to retrieve user meta');
        }

        return $this->user_meta[$meta_key];

    }

    /*
     * |--------------------------------------------------------------------------
     * | Tenant user meta
     * |--------------------------------------------------------------------------
     */

    private array $tenant_user_meta = [];

    /**
     * @param string $tenant_id
     * @param string $meta_key
     * @return void
     * @throws UnexpectedException
     */
    private function defineTenantUserMeta(string $tenant_id, string $meta_key): void
    {

        if (isset($this->tenant_user_meta[$tenant_id][$meta_key])) {
            return;
        }

        $tenant_user_id = $this->getTenantUserId($tenant_id);

        if ($tenant_user_id === null) {
            $this->tenant_user_meta[$tenant_id][$meta_key] = null;
            return;
        }

        $tenantUserMetaModel = new TenantUserMetaModel($this->rbacService);

        try {
            $meta = $tenantUserMetaModel->findByKey($tenant_user_id, $meta_key);
            $this->tenant_user_meta[$tenant_id][$meta_key] = $meta->get('meta_value');
        } catch (DoesNotExistException) {
            $this->tenant_user_meta[$tenant_id][$meta_key] = null;
        }

    }

    /**
     * Get tenant user meta by meta key, or null if not existing.
     *
     * @param string $tenant_id
     * @param string $meta_key
     * @return string|null
     * @throws UnexpectedException
     */
    public function getTenantUserMeta(string $tenant_id, string $meta_key): ?string
    {

        try {
            $this->defineTenantUserMeta($tenant_id, $meta_key);
        } catch (OrmServiceException) {
            throw new UnexpectedException('Unable to retrieve tenant user meta');
        }

        return $this->tenant_user_meta[$tenant_id][$meta_key];

    }

}