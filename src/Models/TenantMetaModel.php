<?php

namespace Bayfront\BonesService\Rbac\Models;

use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Rbac\Abstracts\RbacModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\BonesService\Rbac\Traits\HasProtectedPrefix;
use Bayfront\SimplePdo\Query;

class TenantMetaModel extends RbacModel
{

    use HasProtectedPrefix;

    /**
     * The container will resolve any dependencies.
     * OrmService and Db are required by the abstract model.
     *
     * @param RbacService $rbacService
     */

    public function __construct(RbacService $rbacService)
    {
        parent::__construct($rbacService, $rbacService::TABLE_TENANT_META);
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
        'tenant' => TenantsModel::class
    ];

    /**
     * Fields which are required when creating resource.
     *
     * @var array
     */
    protected array $required_fields = [
        'tenant',
        'meta_key',
        'meta_value'
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
        'meta_key' => 'isString|maxLength:255',
        'meta_value' => 'maxLength:4000000000'
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
            'meta_key'
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
        'meta_key',
        'meta_value',
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
        'meta_key',
        'meta_value'
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

    }

    /**
     * Filter query before reading resource(s).
     *
     * - Filter protected meta prefix
     *
     * @param Query $query
     * @return Query
     * @throws UnexpectedException
     */
    protected function onReading(Query $query): Query
    {
        return $this->filterProtectedPrefixReading($query); // Trait: HasProtectedPrefix
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
     * @param OrmResource $existing
     * @param array $fields (Fields to update)
     * @return array
     */
    protected function onUpdating(OrmResource $existing, array $fields): array
    {
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

    }

    /**
     * Filter fields before writing to resource (creating and updating).
     *
     * - Filter protected meta prefix
     *
     * @param array $fields
     * @return array
     * @throws InvalidFieldException
     */
    protected function onWriting(array $fields): array
    {
        return $this->filterProtectedPrefixWriting($fields); // Trait: HasProtectedPrefix
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
     * - Filter protected meta prefix
     *
     * @param OrmResource $resource
     * @return void
     * @throws InvalidFieldException
     */
    protected function onDeleting(OrmResource $resource): void
    {
        $this->filterProtectedPrefixDeleting($resource); // Trait: HasProtectedPrefix
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
     * - Reset protected meta prefix filters
     *
     * @param string $function (Function which completed)
     * @return void
     */
    protected function onComplete(string $function): void
    {
        $this->resetProtectedPrefixFilter(); // Trait: HasProtectedPrefix
    }

    /*
     * |--------------------------------------------------------------------------
     * | Traits
     * |--------------------------------------------------------------------------
     */

    /*
     * |--------------------------------------------------------------------------
     * | Model-specific
     * |--------------------------------------------------------------------------
     */

    /**
     * Find tenant meta by tenant ID and meta key value.
     *
     * @param string $tenant_id
     * @param string $meta_key
     * @return OrmResource
     * @throws DoesNotExistException
     * @throws UnexpectedException
     */
    public function findByKey(string $tenant_id, string $meta_key): OrmResource
    {

        $meta_id = $this->ormService->db->single("SELECT id FROM $this->table_name WHERE tenant = :tenantId AND meta_key = :metaKey", [
            'tenantId' => $tenant_id,
            'metaKey' => $meta_key
        ]);

        if (!$meta_id) {
            throw new DoesNotExistException('Unable to find tenant meta: Meta does not exist');
        }

        return $this->find($meta_id);

    }

}