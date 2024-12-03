<?php

namespace Bayfront\BonesService\Rbac;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Abstracts\Service;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Exceptions\ServiceException;
use Bayfront\BonesService\Orm\OrmService;
use Bayfront\BonesService\Rbac\Events\RbacServiceEvents;
use Bayfront\BonesService\Rbac\Exceptions\RbacServiceException;
use Bayfront\BonesService\Rbac\Filters\RbacServiceFilters;
use Bayfront\StringHelpers\Str;

class RbacService extends Service
{

    public OrmService $ormService;
    private array $config;

    /**
     * The container will resolve any dependencies.
     * EventService is required by the abstract service.
     *
     * @param OrmService $ormService
     * @param array $config
     * @throws RbacServiceException
     */
    public function __construct(OrmService $ormService, array $config = [])
    {
        $this->ormService = $ormService;
        $this->config = $config;

        parent::__construct($this->ormService->events);

        // Enqueue events

        try {
            $this->ormService->events->addSubscriptions(new RbacServiceEvents($this));
        } catch (ServiceException $e) {
            throw new RbacServiceException('Unable to start RbacService: ' . $e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        // Enqueue filters

        try {
            $this->ormService->filters->addSubscriptions(new RbacServiceFilters($this));
        } catch (ServiceException $e) {
            throw new RbacServiceException('Unable to start RbacService: ' . $e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        $this->ormService->events->doEvent('rbac.start', $this);

    }

    // Database tables
    public const TABLE_PERMISSIONS = 'permissions';
    public const TABLE_TENANT_INVITATIONS = 'tenant_invitations';
    public const TABLE_TENANT_META = 'tenant_meta';
    public const TABLE_TENANT_ROLE_PERMISSIONS = 'tenant_role_permissions';
    public const TABLE_TENANT_ROLES = 'tenant_roles';
    public const TABLE_TENANT_TEAMS = 'tenant_teams';
    public const TABLE_TENANT_USER_META = 'tenant_user_meta';
    public const TABLE_TENANT_USER_ROLES = 'tenant_user_roles';
    public const TABLE_TENANT_USER_TEAMS = 'tenant_user_teams';
    public const TABLE_TENANT_USERS = 'tenant_users';
    public const TABLE_TENANTS = 'tenants';
    public const TABLE_USER_KEYS = 'user_keys';
    public const TABLE_USER_META = 'user_meta';
    public const TABLE_USERS = 'users';

    /**
     * Get RBAC service configuration value in dot notation.
     *
     * @param string $key (Key to return in dot notation)
     * @param mixed|null $default (Default value to return if not existing)
     * @return mixed
     */
    public function getConfig(string $key = '', mixed $default = null): mixed
    {
        return Arr::get($this->config, $key, $default);
    }

    /**
     * Get prefixed table name.
     *
     * @param string $table (Valid TABLE_* constant)
     * @return string
     */
    public function getTableName(string $table): string
    {
        return $this->getConfig('table_prefix', '') . $table;
    }

    /**
     * Create hash from raw value.
     *
     * @param string $raw_value
     * @return string
     */
    public function createHash(string $raw_value): string
    {
        return App::createHash($raw_value, App::getConfig('app.key', ''));
    }

    /**
     * Does hash match raw value?
     *
     * @param string $hash
     * @param string $raw_value
     * @return bool
     */
    public function hashMatches(string $hash, string $raw_value): bool
    {
        return $hash === App::createHash($raw_value, App::getConfig('app.key', ''));
    }

    // TOTP types
    public const TOTP_TYPE_NONZERO = 'nonzero';
    public const TOTP_TYPE_ALPHA = 'alpha';
    public const TOTP_TYPE_NUMERIC = 'numeric';
    public const TOTP_TYPE_ALPHANUMERIC = 'alphanumeric';
    public const TOTP_TYPE_ALL = 'all';

    /**
     * Create TOTP (Time-based one-time password).
     * Returns raw value. How the TOTP is handled is determined at the app-level.
     *
     * @param int $duration (Validity duration, in minutes. 0 for no expiration.)
     * @param int $length
     * @param string $type (Any TOTP_TYPE_* constant)
     * @return Totp
     */
    public function createTotp(int $duration = 0, int $length = 6, string $type = self::TOTP_TYPE_NUMERIC): Totp
    {

        $now = time();

        if ($duration === 0) {
            $expires_at = 0;
        } else {
            $expires_at = $now + ($duration * 60);
        }

        return new Totp([
            'created_at' => $now,
            'expires_at' => $expires_at,
            'value' => Str::random($length, $type)
        ]);

    }

    /**
     * Is TOTP expired?
     *
     * @param Totp $totp
     * @return bool
     */
    public function totpIsExpired(Totp $totp): bool
    {

        if ($totp->getExpiresAt() !== 0 && $totp->getExpiresAt() > time()) {
            return false;
        }

        return true;

    }

}