<?php

namespace Bayfront\BonesService\Rbac\Models;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Orm\Traits\Castable;
use Bayfront\BonesService\Orm\Traits\HasNullableJsonField;
use Bayfront\BonesService\Orm\Traits\HasOmittedFields;
use Bayfront\BonesService\Rbac\Abstracts\RbacModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\SimplePdo\Query;
use Bayfront\TimeHelpers\Time;
use Exception;

class UsersModel extends RbacModel
{

    use Castable, HasNullableJsonField, HasOmittedFields;

    /**
     * The container will resolve any dependencies.
     * OrmService and Db are required by the abstract model.
     *
     * @param RbacService $rbacService
     */

    public function __construct(RbacService $rbacService)
    {
        parent::__construct($rbacService, $rbacService::TABLE_USERS);
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
    protected array $related_fields = [];

    /**
     * Fields which are required when creating resource.
     *
     * @var array
     */
    protected array $required_fields = [
        'email',
        'password'
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
        'email' => 'email|maxLength:255',
        'password' => 'isString|maxLength:255',
        'meta' => 'isArray',
        'admin' => 'isBoolean',
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
        'email'
    ];

    /**
     * Fields which can be read from the resource.
     *
     * @var array
     */
    protected array $allowed_fields_read = [
        'id',
        'email',
        'meta',
        'admin',
        'enabled',
        'created_at',
        'updated_at',
        'verified_at'
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
        'email',
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
     * - Create salt
     * - Hash password
     * - Remove meta keys with null value
     *
     * @param array $fields
     * @return array
     * @throws UnexpectedException
     * @throws InvalidFieldException
     */
    protected function onCreating(array $fields): array
    {
        $fields['id'] = $this->createUuid();

        // $fields['password'] already required and validated

        try {
            $fields['salt'] = App::createKey();
        } catch (Exception) {
            throw new UnexpectedException('Unable to create user: Error creating salt');
        }

        $fields['password'] = App::createPasswordHash($this->ormService->filters->doFilter('rbac.user.password', Arr::get($fields, 'password', '')), $fields['salt']);

        if (isset($fields['meta']) && is_array($fields['meta'])) {
            $fields['meta'] = $this->defineNullableJsonField($fields['meta']);
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
        $this->ormService->events->doEvent('rbac.user.created', $resource);
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
     * - Transform fields
     *
     * @param array $fields
     * @return array
     * @throws UnexpectedException
     */
    protected function onRead(array $fields): array
    {
        return $this->transform($fields, [
            'meta' => [$this, 'jsonDecode'],
            'admin' => [$this, 'boolean'],
            'enabled' => [$this, 'boolean']
        ]);
    }

    /**
     * Filter fields before updating resource.
     *
     * - Hash password if exists
     * - Merge meta if exists
     *
     * @param OrmResource $existing
     * @param array $fields (Fields to update)
     * @return array
     * @throws DoesNotExistException
     * @throws InvalidFieldException
     */
    protected function onUpdating(OrmResource $existing, array $fields): array
    {

        if (isset($fields['password'])) {

            $salt = $this->ormService->db->single("SELECT salt FROM $this->table_name WHERE $this->primary_key = :id", [
                'id' => $existing->getPrimaryKey()
            ]);

            if (!$salt) { // This should never happen
                throw new DoesNotExistException('Unable to update user: User salt does not exist');
            }

            $fields['password'] = App::createPasswordHash($this->ormService->filters->doFilter('rbac.user.password', $fields['password']), $salt);

        }

        if (isset($fields['meta']) && is_array($fields['meta'])) {
            $fields['meta'] = $this->updateNullableJsonField($this->ormService, $this->table_name, $this->primary_key, $existing->getPrimaryKey(), $this->getNullableJsonField(), $fields['meta']);
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

        $this->ormService->events->doEvent('rbac.user.updated', $resource, $previous, $fields);

        if (isset($fields['email'])) {
            $this->ormService->events->doEvent('rbac.user.email.updated', $resource);
        }

        if (isset($fields['password'])) {
            $this->ormService->events->doEvent('rbac.user.password.updated', $resource);
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
            'meta' => [$this, 'jsonEncode'],
            'admin' => [$this, 'integer'],
            'enabled' => [$this, 'integer'],
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
     * - Ensure user does not own tenant
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
        } catch (Exception) {
            throw new UnexpectedException('Unable to delete user: Error validating tenants');
        }

        $owned = $this->ormService->db->count($tenantsModel->getTableName(), [
            'owner' => $resource->getPrimaryKey()
        ]);

        if ($owned > 0) {
            throw new InvalidRequestException('Unable to delete user: Tenant owners cannot be deleted');
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
        $this->ormService->events->doEvent('rbac.user.deleted', $resource);
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
     * Trait: HasNullableJsonField
     *
     * @inheritDoc
     */
    public function getNullableJsonField(): string
    {
        return 'meta';
    }

    /**
     * Trait: HasOmittedFields
     *
     * @inheritDoc
     */
    public function getOmittedFields(): array
    {
        return [
            'password',
            'salt'
        ];
    }

    /*
     * |--------------------------------------------------------------------------
     * | Model-specific
     * |--------------------------------------------------------------------------
     */

    /**
     * Find user by email.
     *
     * @param string $email
     * @return OrmResource
     * @throws DoesNotExistException
     * @throws UnexpectedException
     */
    public function findByEmail(string $email): OrmResource
    {

        $user = $this->ormService->db->single("SELECT id FROM $this->table_name WHERE email = :email", [
            'email' => $email
        ]);

        if (!$user) {
            throw new DoesNotExistException('Unable to find user: User does not exist');
        }

        return $this->find($user);

    }

    // ------------------------Verification -------------------------

    /**
     * Update verified_at field to null.
     *
     * @param string $email
     * @return bool
     */
    public function unverify(string $email): bool
    {

        return $this->ormService->db->update($this->table_name, [
            'verified_at' => null
        ], [
            'email' => $email
        ]);

    }

    /**
     * Update verified_at field to current datetime.
     *
     * @param string $email
     * @return bool
     */
    public function verify(string $email): bool
    {

        $updated = $this->ormService->db->update($this->table_name, [
            'verified_at' => Time::getDateTime()
        ], [
            'email' => $email
        ]);

        if ($updated === true) {
            $this->ormService->events->doEvent('rbac.user.verified', $email);
        }

        return $updated;

    }

    /**
     * Delete all unverified users created and never updated.
     *
     * NOTE:
     * When $new_users_only is false, existing users who update their email address but have not yet
     * verified it will be removed.
     *
     * @param int $timestamp
     * @param bool $new_users_only (When false, users last updated before the timestamp will also be removed)
     * @return void
     * @throws UnexpectedException
     */
    public function deleteUnverified(int $timestamp, bool $new_users_only = true): void
    {

        if ($this->rbacService->getConfig('user.require_verification', true) === false) {
            return;
        }

        $table = $this->getTableName();
        $datetime = date('Y-m-d H:i:s', $timestamp);

        if ($new_users_only === true) {

            $unverified = $this->ormService->db->select("SELECT id FROM $table WHERE created_at < :datetime AND updated_at IS NULL AND verified_at IS NULL", [
                'datetime' => $datetime
            ]);

        } else {

            $unverified = $this->ormService->db->select("SELECT id FROM $table WHERE created_at < :datetime AND (updated_at IS NULL OR updated_at < :datetime) AND verified_at IS NULL", [
                'datetime' => $datetime
            ]);

        }

        foreach ($unverified as $uv) {
            $this->delete($uv['id']);
        }

    }

}