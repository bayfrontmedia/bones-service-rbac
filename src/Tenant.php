<?php

namespace Bayfront\BonesService\Rbac;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\OrmServiceException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Rbac\Models\TenantMetaModel;

/**
 * Read-only tenant.
 */
class Tenant
{

    public RbacService $rbacService;
    public OrmResource $ormResource;
    private array $tenant;

    /**
     * @param RbacService $rbacService
     * @param OrmResource $ormResource (TenantsModel resource)
     */
    public function __construct(RbacService $rbacService, OrmResource $ormResource)
    {
        $this->rbacService = $rbacService;
        $this->ormResource = $ormResource;
        $this->tenant = $ormResource->read();
    }

    /*
     * |--------------------------------------------------------------------------
     * | Tenant
     * |--------------------------------------------------------------------------
     */

    /**
     * Get tenant resource.
     *
     * @return OrmResource
     */
    public function getResource(): OrmResource
    {
        return $this->ormResource;
    }

    /**
     * Get entire tenant as an object.
     *
     * @return object
     */
    public function asObject(): object
    {
        return $this->ormResource->asObject();
    }

    /**
     * Get entire tenant array.
     *
     * @return array
     */
    public function read(): array
    {
        return $this->tenant;
    }

    /**
     * Get single tenant field.
     *
     * @param string $field
     * @param mixed $default (Default value to return)
     * @return mixed
     */
    public function get(string $field, mixed $default = null): mixed
    {
        return Arr::get($this->tenant, $field, $default);
    }

    /**
     * Get tenant ID.
     *
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->ormResource->getPrimaryKey();
    }

    /**
     * Get owner user ID.
     *
     * @return string
     */
    public function getOwner(): string
    {
        return Arr::get($this->tenant, 'owner', '');
    }

    /**
     * Get domain.
     *
     * @return string
     */
    public function getDomain(): string
    {
        return Arr::get($this->tenant, 'domain', '');
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return Arr::get($this->tenant, 'name', '');
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
        return Arr::get(Arr::get($this->tenant, 'meta', []), $key, $default);
    }

    /**
     * Is tenant enabled?
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return Arr::get($this->tenant, 'enabled', false);
    }

    /*
     * |--------------------------------------------------------------------------
     * | Tenant meta
     * |--------------------------------------------------------------------------
     */

    private array $tenant_meta = [];

    /**
     *
     * @param string $meta_key
     * @return void
     * @throws UnexpectedException
     */
    private function defineTenantMeta(string $meta_key): void
    {

        if (isset($this->tenant_meta[$meta_key])) {
            return;
        }

        $tenantMetaModel = new TenantMetaModel($this->rbacService);

        try {
            $meta = $tenantMetaModel->findByKey($this->getId(), $meta_key);
            $this->tenant_meta[$meta_key] = $meta->get('meta_value');
        } catch (DoesNotExistException) {
            $this->tenant_meta[$meta_key] = null;
        }

    }

    /**
     * Get tenant meta by meta key, or NULL if not existing.
     *
     * @param string $meta_key
     * @return string|null
     * @throws UnexpectedException
     */
    public function getTenantMeta(string $meta_key): ?string
    {

        try {
            $this->defineTenantMeta($meta_key);
        } catch (OrmServiceException) {
            throw new UnexpectedException('Unable to retrieve tenant meta');
        }

        return $this->tenant_meta[$meta_key];

    }

}