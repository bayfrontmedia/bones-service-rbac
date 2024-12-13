<?php

namespace Bayfront\BonesService\Rbac\Models;

use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\OrmServiceException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Rbac\Abstracts\RbacModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\SimplePdo\Query;
use Exception;

class TenantUsersModel extends RbacModel
{

    /**
     * The container will resolve any dependencies.
     * OrmService and Db are required by the abstract model.
     *
     * @param RbacService $rbacService
     */

    public function __construct(RbacService $rbacService)
    {
        parent::__construct($rbacService, $rbacService::TABLE_TENANT_USERS);
    }

    /**
     * Table name.
     *
     * @var string
     */
    protected string $table_name;

    /**
     * Primary key field.
     * This field must be readable.
     *
     * @var string
     */
    protected string $primary_key = 'id';

    /**
     * Unique, sequential field to use for cursor-based pagination.
     * This field must be readable.
     *
     * @var string
     */
    protected string $cursor_field = 'id';

    /**
     * Related field definitions as:
     * column => ResourceModel::class
     *
     * This associates the column in this model's table with the primary key of the related ResourceModel.
     *
     * @var array
     */
    protected array $related_fields = [
        'tenant' => TenantsModel::class,
        'user' => UsersModel::class
    ];

    /**
     * Fields which are required when creating resource.
     *
     * @var array
     */
    protected array $required_fields = [
        'tenant',
        'user'
    ];

    /**
     * Rules for any fields which can be written to the resource.
     * If a field is required, use $required_fields instead.
     *
     * See: https://github.com/bayfrontmedia/php-validator/blob/master/docs/validator.md
     *
     * @var array
     */
    protected array $allowed_fields_write = [
        'tenant' => 'isString|lengthEquals:36',
        'user' => 'isString|lengthEquals:36'
    ];

    /**
     * Unique fields whose values are checked on create/update.
     * The database is queried once for each key.
     *
     * Uniqueness of a single field as a string, or across multiple fields as an array.
     *
     * @var array
     */
    protected array $unique_fields = [
        [
            'tenant',
            'user'
        ]
    ];

    /**
     * Fields which can be read from the resource.
     *
     * @var array
     */
    protected array $allowed_fields_read = [
        'id',
        'tenant',
        'user',
        'created_at',
        'updated_at'
    ];

    /**
     * Fields which are searched. These fields must be readable.
     * For best performance, all searchable fields should be indexed.
     *
     * When empty, all readable fields will be used.
     *
     * @var array
     */
    protected array $search_fields = [
        'id',
        'tenant',
        'user'
    ];

    /**
     * Maximum related field depth allowed to query.
     * If set, this value overrides the ORM service config value.
     *
     * @var int
     */
    protected int $max_related_depth;

    /**
     * Default query limit when none is specified.
     * If set, this value overrides the ORM service config value.
     *
     * @var int
     */
    protected int $default_limit;

    /**
     * Maximum limit allowed to query, or -1 for unlimited.
     * If set, this value overrides the ORM service config value.
     *
     * @var int
     */
    protected int $max_limit;

    /*
     * |--------------------------------------------------------------------------
     * | Actions
     * |--------------------------------------------------------------------------
     */

    /**
     * Filter fields before creating resource.
     *
     * - Create UUID
     *
     * @param array $fields
     * @return array
     */
    protected function onCreating(array $fields): array
    {
        $fields['id'] = $this->createUuid();
        return $fields;
    }

    /**
     * Actions to perform after a resource is created.
     *
     * @param OrmResource $resource
     * @return void
     */
    protected function onCreated(OrmResource $resource): void
    {
        $this->ormService->events->doEvent('rbac.tenant.user.created', $resource);
    }

    /**
     * Filter query before reading resource(s).
     *
     * @param Query $query
     * @return Query
     */
    protected function onReading(Query $query): Query
    {
        return $query;
    }

    /**
     * Filter fields after a resource is read.
     *
     * @param array $fields
     * @return array
     */
    protected function onRead(array $fields): array
    {
        return $fields;
    }

    /**
     * Filter fields before updating resource.
     *
     * - Ensure tenant owner is not removed from tenant
     *
     * @param OrmResource $existing
     * @param array $fields (Fields to update)
     * @return array
     * @throws InvalidFieldException
     * @throws UnexpectedException
     */
    protected function onUpdating(OrmResource $existing, array $fields): array
    {

        try {

            $tenantsModel = new TenantsModel($this->rbacService);
            $tenant_owner = $tenantsModel->getOwnerId($existing->get('tenant', ''));

        } catch (Exception) {
            throw new UnexpectedException('Unable to update tenant user: Error validating tenant owner');
        }

        if (isset($fields['tenant'])) {

            if ($existing->get('user') == $tenant_owner && $fields['tenant'] !== $existing->get('tenant')) {
                throw new InvalidFieldException('Unable to update tenant user: Tenant owner cannot be removed');
            }

        }

        if (isset($fields['user'])) {

            if ($existing->get('user') == $tenant_owner && $fields['user'] !== $tenant_owner) {
                throw new InvalidFieldException('Unable to update tenant user: Tenant owner cannot be removed');
            }

        }

        return $fields;

    }

    /**
     * Actions to perform after a resource is updated.
     *
     * @param OrmResource $resource (Newly updated resource)
     * @param OrmResource $previous (Previously existing resource)
     * @param array $fields (Updated fields)
     * @return void
     */
    protected function onUpdated(OrmResource $resource, OrmResource $previous, array $fields): void
    {
        $this->ormService->events->doEvent('rbac.tenant.user.updated', $resource, $previous, $fields);
    }

    /**
     * Filter fields before writing to resource (creating and updating).
     *
     * @param array $fields
     * @return array
     */
    protected function onWriting(array $fields): array
    {
        return $fields;
    }

    /**
     * Actions to perform after a resource is written (created and updated).
     *
     * @param OrmResource $resource
     * @return void
     */
    protected function onWritten(OrmResource $resource): void
    {

    }

    /**
     * Actions to perform before a resource is deleted.
     *
     * - Ensure tenant owner is not removed from tenant
     *
     * @param OrmResource $resource
     * @return void
     * @throws InvalidRequestException
     * @throws UnexpectedException
     */
    protected function onDeleting(OrmResource $resource): void
    {

        try {

            $tenantsModel = new TenantsModel($this->rbacService);
            $tenant_owner = $tenantsModel->getOwnerId($resource->get('tenant', ''));

        } catch (OrmServiceException) {
            throw new UnexpectedException('Unable to delete tenant user: Error validating tenant owner');
        }

        if ($resource->get('user') == $tenant_owner) {
            throw new InvalidRequestException('Unable to delete tenant user: Tenant owner cannot be removed');
        }

    }

    /**
     * Actions to perform after a resource is deleted.
     *
     * @param OrmResource $resource
     * @return void
     */
    protected function onDeleted(OrmResource $resource): void
    {
        $this->ormService->events->doEvent('rbac.tenant.user.deleted', $resource);
    }

    /**
     * Called before any actionable ResourceModel function is executed.
     * Functions executed inside another are ignored.
     * The name of the function is passed as a parameter.
     *
     * @param string $function (Function which began)
     * @return void
     */
    protected function onBegin(string $function): void
    {

    }

    /**
     * Called after any actionable ResourceModel function is executed.
     * Functions executed inside another are ignored.
     * The name of the function is passed as a parameter.
     *
     * @param string $function (Function which completed)
     * @return void
     */
    protected function onComplete(string $function): void
    {

    }

    /*
     * |--------------------------------------------------------------------------
     * | Model-specific
     * |--------------------------------------------------------------------------
     */

    /**
     * Find tenant user by tenant and user ID.
     *
     * Can be used with the SoftDeletes trait trashed filters.
     *
     * @param string $tenant_id
     * @param string $user_id
     * @return OrmResource
     * @throws DoesNotExistException
     * @throws UnexpectedException
     */
    public function findByUserId(string $tenant_id, string $user_id): OrmResource
    {

        $tenant_user_id = $this->ormService->db->single("SELECT id FROM $this->table_name WHERE tenant = :tenantId AND user = :userId", [
            'tenantId' => $tenant_id,
            'userId' => $user_id
        ]);

        if (!$tenant_user_id) {
            throw new DoesNotExistException('Unable to find tenant user: User does not exist');
        }

        return $this->find($tenant_user_id);

    }

    /**
     * Is user in tenant?
     *
     * @param string $tenant_id
     * @param string $user_id
     * @return bool
     */
    public function userInTenant(string $tenant_id, string $user_id): bool
    {

        return $this->ormService->db->exists($this->table_name, [
            'tenant' => $tenant_id,
            'user' => $user_id
        ]);

    }

    /**
     * Is tenant user in tenant?
     *
     * @param string $tenant_id
     * @param string $tenant_user_id
     * @return bool
     */
    public function tenantUserInTenant(string $tenant_id, string $tenant_user_id): bool
    {

        return $this->ormService->db->exists($this->table_name, [
            'tenant' => $tenant_id,
            'id' => $tenant_user_id
        ]);

    }

}