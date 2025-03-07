<?php

namespace Bayfront\BonesService\Rbac\Models;

use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Orm\Traits\Castable;
use Bayfront\BonesService\Orm\Traits\HasOmittedFields;
use Bayfront\BonesService\Orm\Traits\Prunable;
use Bayfront\BonesService\Rbac\Abstracts\RbacModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\SimplePdo\Query;
use Bayfront\StringHelpers\Str;
use Bayfront\TimeHelpers\Time;

class UserKeysModel extends RbacModel
{

    use Castable, HasOmittedFields, Prunable;

    /**
     * The container will resolve any dependencies.
     * OrmService and Db are required by the abstract model.
     *
     * @param RbacService $rbacService
     */

    public function __construct(RbacService $rbacService)
    {
        parent::__construct($rbacService, $rbacService::TABLE_USER_KEYS);
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
        'user' => UsersModel::class
    ];

    /**
     * Fields which are required when creating resource.
     *
     * @var array
     */
    protected array $required_fields = [
        'user',
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
        'user' => 'isString|lengthEquals:36',
        'name' => 'isString|maxLength:255',
        'allowed_domains' => 'isArray',
        'allowed_ips' => 'isArray',
        'expires_at' => 'date:Y-m-d H:i:s'
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
            'user',
            'name'
        ]
    ];

    /**
     * Fields which can be read from the resource.
     *
     * @var array
     */
    protected array $allowed_fields_read = [
        'id',
        'user',
        'name',
        'allowed_domains',
        'allowed_ips',
        'expires_at',
        'last_used',
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
        'user',
        'name',
        'allowed_domains',
        'allowed_ips'
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
     * - Create key, ensuring uniqueness
     * - Ensure expires_at is set and valid
     *
     * @param array $fields
     * @return array
     * @throws InvalidFieldException
     */
    protected function onCreating(array $fields): array
    {

        $fields['id'] = $this->createUuid();

        $fields['key_value'] = $this->createNewKey();

        if (!isset($fields['expires_at'])) {

            $fields['expires_at'] = Time::getDateTime(time() + ($this->rbacService->getConfig('user.key.max_mins', 0) * 60));

        } else if ($this->rbacService->getConfig('user.key.max_mins', 0) > 0) {

            $exp = strtotime($fields['expires_at']);

            if ($exp < time()
                || $exp > time() + ($this->rbacService->getConfig('user.key.max_mins', 0) * 60)) {
                throw new InvalidFieldException('Unable to create user key: Expiration is not valid');
            }

        }

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
        $this->ormService->events->doEvent('rbac.user.key.created', $resource);
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
     * - Return raw key value once when created
     * - Transform fields
     *
     * @param array $fields
     * @return array
     * @throws UnexpectedException
     */
    protected function onRead(array $fields): array
    {

        if ($this->raw_key !== '') {
            $fields['key_value'] = $this->raw_key;
            $this->raw_key = ''; // Reset
        }

        return $this->transform($fields, [
            'allowed_domains' => [$this, 'jsonDecode'],
            'allowed_ips' => [$this, 'jsonDecode']
        ]);
    }

    /**
     * Filter fields before updating resource.
     *
     * - Do not allow updating expires_at field
     *
     * @param OrmResource $existing
     * @param array $fields (Fields to update)
     * @return array
     * @throws InvalidFieldException
     */
    protected function onUpdating(OrmResource $existing, array $fields): array
    {

        if (isset($fields['expires_at'])) {
            throw new InvalidFieldException('Unable to update user key: Expiration cannot be modified');
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
        $this->ormService->events->doEvent('rbac.user.key.updated', $resource, $previous, $fields);
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
            'allowed_domains' => [$this, 'jsonEncode'],
            'allowed_ips' => [$this, 'jsonEncode'],
            'expires_at' => [$this, 'datetime']
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
        $this->ormService->events->doEvent('rbac.user.key.deleted', $resource);
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
     * Trait: HasOmittedFields
     *
     * @inheritDoc
     */
    public function getOmittedFields(): array
    {
        return [
            'key_value'
        ];
    }

    /**
     * Trait: Prunable
     *
     * @inheritDoc
     */
    protected function getPruneField(): string
    {
        return 'expires_at';
    }

    /*
     * |--------------------------------------------------------------------------
     * | Model-specific
     * |--------------------------------------------------------------------------
     */

    private string $raw_key = '';

    private function createNewKey(): string
    {

        $raw_key = Str::random(36, 'alphanumeric');
        $hashed_key = App::createHash($raw_key, App::getConfig('app.key', ''), 'sha256', true);

        if ($this->hashedKeyExists($hashed_key)) {
            return $this->createNewKey();
        }

        $this->raw_key = $raw_key;
        return $hashed_key;

    }

    /**
     * Does a user key exist with hashed key value?
     *
     * @param string $hashed_key
     * @return bool
     */
    private function hashedKeyExists(string $hashed_key): bool
    {

        return $this->ormService->db->exists($this->table_name, [
            'key_value' => $hashed_key
        ]);

    }

    /**
     * Find user key by key value.
     *
     * @param string $key
     * @return OrmResource
     * @throws DoesNotExistException
     * @throws UnexpectedException
     */
    public function findByKey(string $key): OrmResource
    {

        $key_id = $this->ormService->db->single("SELECT id FROM $this->table_name WHERE key_value = :keyValue", [
            'keyValue' => App::createHash($key, App::getConfig('app.key', ''), 'sha256', true)
        ]);

        if (!$key_id) {
            throw new DoesNotExistException('Unable to find user key: Key does not exist');
        }

        return $this->find($key_id);

    }

}