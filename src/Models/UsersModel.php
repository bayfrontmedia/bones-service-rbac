<?php

namespace Bayfront\BonesService\Rbac\Models;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Orm\Traits\Castable;
use Bayfront\BonesService\Orm\Traits\SoftDeletes;
use Bayfront\BonesService\Rbac\Abstracts\RbacModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\SimplePdo\Query;
use Bayfront\StringHelpers\Str;
use Bayfront\TimeHelpers\Time;
use Bayfront\Validator\Rules\IsJson;
use Exception;

class UsersModel extends RbacModel
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
     * Rules for any fields which can be written to the resource.
     *
     * See: https://github.com/bayfrontmedia/php-validator/blob/master/docs/validator.md
     *
     * @var array
     */
    protected array $allowed_fields_write = [
        'email' => 'required|email|maxLength:255',
        'password' => 'required|isString|maxLength:255',
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
        'verified_at',
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
     *
     * @param array $fields
     * @return array
     * @throws UnexpectedException
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
     *
     * @param OrmResource $existing
     * @param array $fields (Fields to update)
     * @return array
     * @throws DoesNotExistException
     */
    protected function onUpdating(OrmResource $existing, array $fields): array
    {

        if (isset($fields['password'])) {

            $salt = $this->rbacService->ormService->db->single("SELECT salt FROM $this->table_name WHERE $this->primary_key = :id", [
                'id' => $existing->getPrimaryKey()
            ]);

            if (!$salt) { // This should never happen
                throw new DoesNotExistException('Unable to update user: User salt does not exist');
            }

            $fields['password'] = App::createPasswordHash($this->ormService->filters->doFilter('rbac.user.password', $fields['password']), $salt);

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
        if (in_array('password', $fields)) {
            $this->rbacService->ormService->events->doEvent('rbac.user.password.updated', $resource);
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
     * Find user by email.
     *
     * Can be used with the SoftDeletes trait trashed filters.
     *
     * @param string $email
     * @return OrmResource
     * @throws DoesNotExistException
     * @throws UnexpectedException
     */
    public function findByEmail(string $email): OrmResource
    {

        $user = $this->rbacService->ormService->db->single("SELECT id FROM $this->table_name WHERE email = :email", [
            'email' => $email
        ]);

        if (!$user) {
            throw new DoesNotExistException('Unable to find user: User does not exist');
        }

        return $this->find($user);

    }

    // ------------------------Verification -------------------------

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
            $this->rbacService->ormService->events->doEvent('rbac.user.verified', $email);
        }

        return $updated;

    }

    /**
     * Soft-delete all unverified users created before timestamp.
     *
     * @param int $timestamp
     * @return void
     * @throws UnexpectedException
     */
    public function deleteUnverified(int $timestamp): void
    {

        if ($this->rbacService->getConfig('user.require_verification', true) === false) {
            return;
        }

        $table = $this->getTableName();
        $datetime = date('Y-m-d H:i:s', $timestamp);

        $unverified = $this->rbacService->ormService->db->select("SELECT id FROM $table WHERE created_at < :datetime AND verified_at IS NULL AND deleted_at IS NULL", [
            'datetime' => $datetime
        ]);

        foreach ($unverified as $uv) {
            $this->delete($uv['id']);
        }

    }

    // ------------------------- MFA -------------------------

    // MFA types
    public const MFA_TYPE_NONZERO = 'nonzero';
    public const MFA_TYPE_ALPHA = 'alpha';
    public const MFA_TYPE_NUMERIC = 'numeric';
    public const MFA_TYPE_ALPHANUMERIC = 'alphanumeric';
    public const MFA_TYPE_ALL = 'all';

    /**
     * Create MFA for non-deleted user, verifying MFA wait time has elapsed.
     *
     * @param string $email
     * @param int $length
     * @param string $type (Any MFA_TYPE_* constant)
     * @return array (Keys: created_at, expires_at, value)
     * @throws AlreadyExistsException
     * @throws DoesNotExistException
     */
    public function createMfa(string $email, int $length = 6, string $type = self::MFA_TYPE_NUMERIC): array
    {

        try {
            $mfa = $this->getMfa($email);
        } catch (DoesNotExistException) {
            // Do nothing
        }

        $now = time();

        $mfa_wait = (int)$this->rbacService->getConfig('user.mfa.wait', 3);

        if (isset($mfa['created_at']) && $mfa_wait > 0) {

            if ((int)$mfa['created_at'] > $now - ($mfa_wait * 60)) {
                throw new AlreadyExistsException('Unable to create user MFA: Wait time not yet elapsed');
            }

        }

        if ($this->rbacService->getConfig('user.mfa.duration', 15) == 0) {
            $expires_at = 0;
        } else {
            $expires_at = $now + ($this->rbacService->getConfig('user.mfa.duration', 15) * 60);
        }

        $mfa_raw = [
            'created_at' => $now,
            'expires_at' => $expires_at,
            'value' => Str::random($length, $type)
        ];

        $mfa_save = $mfa_raw;
        $mfa_save['value'] = App::createHash($mfa_raw['value'], App::getConfig('app.key', ''));

        $updated = $this->ormService->db->update($this->table_name, [ // Ensure user is not deleted
            'mfa' => json_encode($mfa_save)
        ], [
            'email' => $email,
            'deleted_at' => null
        ]);

        if (!$updated) {
            throw new DoesNotExistException('Unable to create user MFA: User does not exist');
        }

        $this->rbacService->ormService->events->doEvent('rbac.user.mfa.created', $mfa_raw);

        return $mfa_raw;

    }

    /**
     * Get non-deleted user MFA, or quietly delete if invalid.
     *
     * @param string $email
     * @return array (Keys: created_at, expires_at, value)
     * @throws DoesNotExistException
     */
    public function getMfa(string $email): array
    {

        $deleted_at_field = $this->getDeletedAtField();

        $mfa = $this->ormService->db->single("SELECT mfa FROM $this->table_name WHERE email = :email AND mfa IS NOT NULL AND $deleted_at_field IS NULL", [
            'email' => $email
        ]);

        if (!$mfa) {
            throw new DoesNotExistException('Unable to get user MFA: MFA does not exist');
        }

        $json = new IsJson($mfa);

        if (!$json->isValid()) {
            $this->deleteMfa($email);
            throw new DoesNotExistException('Unable to get user MFA: MFA is invalid');
        }

        $mfa = json_decode($mfa, true);

        if (!isset($mfa['created_at']) || !isset($mfa['expires_at']) || !isset($mfa['value'])) {
            $this->deleteMfa($email);
            throw new DoesNotExistException('Unable to get user MFA: MFA missing required keys');
        }

        return $mfa;

    }

    /**
     * Is MFA valid?
     * Quietly deletes MFA if expired.
     *
     * @param string $email
     * @param string $value
     * @return bool
     */
    public function mfaIsValid(string $email, string $value): bool
    {

        try {
            $mfa = $this->getMfa($email);
        } catch (DoesNotExistException) {
            return false;
        }

        // MFA is not expired

        if ((int)Arr::get($mfa, 'expires_at', 0) !== 0 && (int)Arr::get($mfa, 'expires_at', 0) < time()) {

            $this->deleteMfa($email);
            return false;

        }

        // MFA value matches

        if (Arr::get($mfa, 'value') != App::createHash($value, App::getConfig('app.key', ''))) {
            return false;
        }

        return true;

    }

    /**
     * Quietly delete user MFA, if existing.
     *
     * @param string $email
     * @return bool
     */
    public function deleteMfa(string $email): bool
    {

        return $this->ormService->db->update($this->table_name, [
            'mfa' => null
        ], [
            'email' => $email
        ]);

    }

    /**
     * Quietly delete all expired MFA's.
     *
     * @return void
     */
    public function deleteExpiredMfas(): void
    {

        $now = time();

        $table = $this->getTableName();

        $mfas = $this->rbacService->ormService->db->select("SELECT id, mfa FROM $table WHERE mfa IS NOT NULL");

        $delete_ids = [];

        foreach ($mfas as $mfa) {

            $validator = new IsJson($mfa['mfa']);

            if (!$validator->isValid()) {

                $delete_ids[] = $mfa['id'];
                continue;
            }

            $mfa_value = json_decode($mfa['mfa'], true);

            if (Arr::get($mfa_value, 'expires_at', 0) < $now) {
                $delete_ids[] = "'" . $mfa['id'] . "'";
            }

        }

        if (!empty($delete_ids)) {
            $this->rbacService->ormService->db->query("UPDATE $table SET mfa = NULL WHERE id IN (" . implode(',', $delete_ids) . ")");
        }

    }

}