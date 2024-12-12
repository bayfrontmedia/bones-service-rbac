<?php

namespace Bayfront\BonesService\Rbac\Models;

use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Orm\Traits\Castable;
use Bayfront\BonesService\Orm\Traits\SoftDeletes;
use Bayfront\BonesService\Rbac\Abstracts\RbacModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\SimplePdo\Query;
use Exception;

class TenantsModel extends RbacModel
{

    use Castable, SoftDeletes;

    /**
     * The container will resolve any dependencies.
     * OrmService and Db are required by the abstract model.
     *
     * @param RbacService $rbacService
     */

    public function __construct(RbacService $rbacService)
    {
        parent::__construct($rbacService, $rbacService::TABLE_TENANTS);
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
        'owner' => UsersModel::class
    ];

    /**
     * Fields which are required when creating resource.
     *
     * @var array
     */
    protected array $required_fields = [
        'owner',
        'domain',
        'name'
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
        'owner' => 'isString|lengthEquals:36',
        'domain' => 'isString|maxLength:63',
        'name' => 'isString|maxLength:255',
        'meta' => 'isArray',
        'enabled' => 'isBoolean'
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
        'domain'
    ];

    /**
     * Fields which can be read from the resource.
     *
     * @var array
     */
    protected array $allowed_fields_read = [
        'id',
        'owner',
        'domain',
        'name',
        'meta',
        'enabled',
        'created_at',
        'updated_at',
        'deleted_at'
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
        'owner',
        'domain',
        'name',
        'meta'
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

        if (isset($fields['meta']) && is_array($fields['meta'])) {

            foreach ($fields['meta'] as $k => $v) {
                if ($v === null) {
                    unset($fields['meta'][$k]);
                }
            }

        }

        return $fields;
    }

    /**
     * Actions to perform after a resource is created.
     *
     * - Add owner to tenant users, or delete tenant on error
     *
     * @param OrmResource $resource
     * @return void
     * @throws UnexpectedException
     */
    protected function onCreated(OrmResource $resource): void
    {

        try {

            $tenantUsersModel = new TenantUsersModel($this->rbacService);

            $tenantUsersModel->create([
                'tenant' => $resource->getPrimaryKey(),
                'user' => $resource->get('owner', '')
            ]);

        } catch (Exception) {

            $this->delete($resource->getPrimaryKey());

            throw new UnexpectedException('Unable to create tenant: Error adding owner to tenant users');

        }

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
     * @throws UnexpectedException
     */
    protected function onRead(array $fields): array
    {
        return $this->transform($fields, [
            'meta' => [$this, 'jsonDecode'],
            'enabled' => [$this, 'boolean']
        ]);
    }

    /**
     * Filter fields before updating resource.
     *
     * - If owner is updated, ensure exists as a tenant user.
     *
     * @param OrmResource $existing
     * @param array $fields (Fields to update)
     * @return array
     * @throws InvalidFieldException
     * @throws UnexpectedException
     */
    protected function onUpdating(OrmResource $existing, array $fields): array
    {

        if (isset($fields['owner']) && $fields['owner'] !== $existing->get('owner')) {

            try {

                $tenantUsersModel = new TenantUsersModel($this->rbacService);

            } catch (Exception) {
                throw new UnexpectedException('Unable to update tenant: Error validating tenant owner');
            }

            if (!$tenantUsersModel->inTenant($existing->getPrimaryKey(), $fields['owner'])) {
                throw new InvalidFieldException('Unable to update tenant: Owner must exist as a tenant user');
            }

        }

        /** @noinspection DuplicatedCode */
        if (isset($fields['meta']) && is_array($fields['meta'])) {

            $meta = $this->ormService->db->single("SELECT meta FROM $this->table_name WHERE $this->primary_key = :id", [
                'id' => $existing->getPrimaryKey()
            ]);

            $meta = array_merge($this->jsonDecode($meta), $fields['meta']);

            foreach ($meta as $k => $v) {
                if ($v === null) {
                    unset($meta[$k]);
                }
            }

            $fields['meta'] = $meta;

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
        if (in_array('owner', $fields)) {
            $this->ormService->events->doEvent('rbac.tenant.owner.updated', $resource, $previous, $fields);
        }
    }

    /**
     * Filter fields before writing to resource (creating and updating).
     *
     * - Transform fields
     *
     * @param array $fields
     * @return array
     * @throws UnexpectedException
     */
    protected function onWriting(array $fields): array
    {
        return $this->transform($fields, [
            'domain' => [$this, 'slug'],
            'meta' => [$this, 'jsonEncode'],
            'enabled' => [$this, 'integer']
        ]);
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
     * @param OrmResource $resource
     * @return void
     */
    protected function onDeleting(OrmResource $resource): void
    {

    }

    /**
     * Actions to perform after a resource is deleted.
     *
     * @param OrmResource $resource
     * @return void
     */
    protected function onDeleted(OrmResource $resource): void
    {

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
     * | Traits
     * |--------------------------------------------------------------------------
     */

    /**
     * Trait: SoftDeletes
     *
     * @inheritDoc
     */
    protected function getDeletedAtField(): string
    {
        return 'deleted_at';
    }

    /*
     * |--------------------------------------------------------------------------
     * | Model-specific
     * |--------------------------------------------------------------------------
     */

    /**
     * Find tenant by domain.
     *
     * Can be used with the SoftDeletes trait trashed filters.
     *
     * @param string $domain
     * @return OrmResource
     * @throws DoesNotExistException
     * @throws UnexpectedException
     */
    public function findByDomain(string $domain): OrmResource
    {

        $tenant_id = $this->ormService->db->single("SELECT id FROM $this->table_name WHERE domain = :domain", [
            'domain' => $domain
        ]);

        if (!$tenant_id) {
            throw new DoesNotExistException('Unable to find tenant: Tenant does not exist');
        }

        return $this->find($tenant_id);

    }

    /**
     * Get tenant owner user ID.
     *
     * @param string $tenant_id
     * @return string
     * @throws DoesNotExistException
     */
    public function getOwnerId(string $tenant_id): string
    {

        $owner = $this->ormService->db->single("SELECT owner FROM $this->table_name WHERE $this->primary_key = :id", [
            'id' => $tenant_id
        ]);

        if (!$owner) {
            throw new DoesNotExistException('Unable to get tenant owner: Tenant owner does not exist');
        }

        return $owner;

    }

}