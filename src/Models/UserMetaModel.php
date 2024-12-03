<?php

namespace Bayfront\BonesService\Rbac\Models;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\OrmServiceException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Orm\Traits\SoftDeletes;
use Bayfront\BonesService\Rbac\Abstracts\RbacModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\BonesService\Rbac\Traits\HasProtectedPrefix;
use Bayfront\JWT\Jwt;
use Bayfront\JWT\TokenException;
use Bayfront\SimplePdo\Query;
use Bayfront\StringHelpers\Str;
use Bayfront\Validator\Rules\IsJson;

class UserMetaModel extends RbacModel
{

    use HasProtectedPrefix, SoftDeletes;

    /**
     * The container will resolve any dependencies.
     * OrmService and Db are required by the abstract model.
     *
     * @param RbacService $rbacService
     */

    public function __construct(RbacService $rbacService)
    {
        parent::__construct($rbacService, $rbacService::TABLE_USER_META);
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
        'user' => 'isString|lengthEquals:36',
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
            'user',
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
        'user',
        'meta_key',
        'meta_value',
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
        'user',
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
     * Find user meta by user ID and meta key value.
     *
     * Can be used with the SoftDeletes trait trashed filters.
     *
     * @param string $user_id
     * @param string $meta_key
     * @return OrmResource
     * @throws DoesNotExistException
     * @throws UnexpectedException
     */
    public function findByKey(string $user_id, string $meta_key): OrmResource
    {

        $meta_id = $this->rbacService->ormService->db->single("SELECT id FROM $this->table_name WHERE user = :userId AND meta_key = :metaKey", [
            'userId' => $user_id,
            'metaKey' => $meta_key
        ]);

        if (!$meta_id) {
            throw new DoesNotExistException('Unable to find user meta: Meta does not exist');
        }

        return $this->find($meta_id);

    }

    // ------------------------- Tokens -------------------------

    public const TOKEN_TYPE_ACCESS = 'access';
    public const TOKEN_TYPE_REFRESH = 'refresh';

    /**
     * Create JWT.
     *
     * @param string $user_id
     * @param string $type
     * @param int $now
     * @param int $exp
     * @param string $jti
     * @return string
     */
    private function createJwt(string $user_id, string $type, int $now, int $exp, string $jti = ''): string
    {

        $jwt = new Jwt(App::getConfig('app.key'));

        $payload = array_merge($this->rbacService->ormService->filters->doFilter('rbac.token.payload', []), [
            'type' => $type
        ]);

        if ($jti !== '') {
            $jwt->jti($jti);
        }

        $jwt->sub($user_id)
            ->iat($now)
            ->nbf($now)
            ->exp($exp);

        return $jwt->encode($payload);

    }

    /**
     * Create token for user.
     *
     * @param string $user_id
     * @param string $type (TOKEN_TYPE_* constant)
     * @return string
     * @throws DoesNotExistException
     * @throws UnexpectedException
     */
    public function createToken(string $user_id, string $type): string
    {

        $now = time();

        if ($type == self::TOKEN_TYPE_ACCESS) {

            $exp = $now + ($this->rbacService->getConfig('user.token.access_duration', 5) * 60);
            $jti = '';

            if ($this->rbacService->getConfig('user.token.revocable') === true) {

                try {

                    $meta = $this->withProtectedPrefix()->upsert([
                        'user' => $user_id,
                        'meta_key' => $this->getProtectedPrefix() . 'access_token',
                        'meta_value' => json_encode([
                            'exp' => $exp
                        ])
                    ]);

                } catch (InvalidFieldException) {
                    throw new UnexpectedException('Unable to create access token: Error saving token');
                }

                $jti = $meta->getPrimaryKey();

            }

            return $this->createJwt($user_id, self::TOKEN_TYPE_ACCESS, $now, $exp, $jti);

        } else if ($type == self::TOKEN_TYPE_REFRESH) {

            $exp = $now + ($this->rbacService->getConfig('user.token.refresh_duration', 10080) * 60);

            try {

                $meta = $this->withProtectedPrefix()->upsert([
                    'user' => $user_id,
                    'meta_key' => $this->getProtectedPrefix() . 'refresh_token',
                    'meta_value' => json_encode([
                        'exp' => $exp
                    ])
                ]);

            } catch (InvalidFieldException) {
                throw new UnexpectedException('Unable to create refresh token: Error saving token');
            }

            return $this->createJwt($user_id, self::TOKEN_TYPE_REFRESH, $now, $exp, $meta->getPrimaryKey());

        } else {
            throw new UnexpectedException('Unable to create token: Invalid type');
        }

    }

    /**
     * Read token payload.
     *
     * NOTE: This does not perform any validation.
     *
     * @param string $token
     * @return array
     * @throws UnexpectedException
     */
    public function readToken(string $token): array
    {
        $jwt = new Jwt(App::getConfig('app.key'));

        try {
            $arr = $jwt->decode($token, false);
        } catch (TokenException) {
            throw new UnexpectedException('Unable to read token payload: Unexpected error');
        }

        return Arr::get($arr, 'payload', []);
    }

    /**
     * Quietly hard-delete token for user.
     *
     * @param string $user_id
     * @param string $type (TOKEN_TYPE_* constant)
     * @return bool
     */
    public function deleteToken(string $user_id, string $type): bool
    {

        $table = $this->getTableName();

        if ($type == self::TOKEN_TYPE_ACCESS) {

            return $this->rbacService->ormService->db->query("DELETE FROM $table WHERE user = :user AND meta_key = :accessToken", [
                'user' => $user_id,
                'accessToken' => $this->getProtectedPrefix() . 'access_token'
            ]);

        } else if ($type == self::TOKEN_TYPE_REFRESH) {

            return $this->rbacService->ormService->db->query("DELETE FROM $table WHERE user = :user AND meta_key = :refreshToken", [
                'user' => $user_id,
                'refreshToken' => $this->getProtectedPrefix() . 'refresh_token'
            ]);

        }

        return false;

    }

    /**
     * Quietly hard-delete access and refresh tokens for user.
     *
     * @param string $user_id
     * @return bool
     */
    public function deleteAllTokens(string $user_id): bool
    {

        $table = $this->getTableName();

        return $this->rbacService->ormService->db->query("DELETE FROM $table WHERE user = :user AND (meta_key = :accessToken OR meta_key = :refreshToken)", [
            'user' => $user_id,
            'accessToken' => $this->getProtectedPrefix() . 'access_token',
            'refreshToken' => $this->getProtectedPrefix() . 'refresh_token'
        ]);

    }

    /**
     * Quietly delete all expired access and refresh tokens.
     *
     * @return void
     */
    public function deleteExpiredTokens(): void
    {

        $now = time();

        $table = $this->getTableName();

        $tokens = $this->rbacService->ormService->db->select("SELECT id, meta_value FROM $table WHERE meta_key = :accessToken OR meta_key = :refreshToken", [
            'accessToken' => $this->getProtectedPrefix() . 'access_token',
            'refreshToken' => $this->getProtectedPrefix() . 'refresh_token'
        ]);

        $delete_ids = [];

        foreach ($tokens as $token) {

            $validator = new IsJson($token['meta_value']);

            if (!$validator->isValid()) {

                $delete_ids[] = $token['id'];
                continue;

            }

            $meta_value = json_decode($token['meta_value'], true);

            if (Arr::get($meta_value, 'exp', 0) < $now) {
                $delete_ids[] = "'" . $token['id'] . "'";
            }

        }

        if (!empty($delete_ids)) {
            $this->rbacService->ormService->db->query("DELETE FROM $table WHERE id IN (" . implode(',', $delete_ids) . ")");
        }

    }
    
    // ------------------------- MFAs -------------------------

    // MFA types
    public const MFA_TYPE_NONZERO = 'nonzero';
    public const MFA_TYPE_ALPHA = 'alpha';
    public const MFA_TYPE_NUMERIC = 'numeric';
    public const MFA_TYPE_ALPHANUMERIC = 'alphanumeric';
    public const MFA_TYPE_ALL = 'all';

    /**
     * Create MFA array for MFA and password requests.
     *
     * @param int $length
     * @param string $type
     * @return array (Keys: created_at, expires_at, value)
     */
    private function createMfaArray(int $length = 6, string $type = self::MFA_TYPE_NUMERIC): array
    {

        $now = time();

        if ($this->rbacService->getConfig('user.mfa.duration', 15) == 0) {
            $expires_at = 0;
        } else {
            $expires_at = $now + ($this->rbacService->getConfig('user.mfa.duration', 15) * 60);
        }

        return [
            'created_at' => $now,
            'expires_at' => $expires_at,
            'value' => Str::random($length, $type)
        ];

    }

    /**
     * Is MFA array valid format and not expired?
     *
     * @param mixed $meta_value
     * @return bool
     */
    private function mfaIsValid(mixed $meta_value): bool
    {

        // Validate format

        $json = new IsJson($meta_value);

        if (!$json->isValid()) {
            return false;
        }

        $meta_value = json_decode($meta_value, true);

        if (!isset($meta_value['created_at']) || !isset($meta_value['expires_at']) || !isset($meta_value['value'])) {
            return false;
        }

        // Validate expired

        if ((int)Arr::get($meta_value, 'expires_at', 0) !== 0 && (int)Arr::get($meta_value, 'expires_at', 0) < time()) {
            return false;
        }

        return true;

    }

    /**
     * Create password request, verifying MFA wait time has elapsed.
     * Value is hashed using RbacService->createHash().
     *
     * @param string $user_id
     * @param int $length
     * @param string $type (Any MFA_TYPE_* constant)
     * @return array (Keys: created_at, expires_at, value)
     * @throws AlreadyExistsException
     * @throws UnexpectedException
     */
    public function createPasswordRequest(string $user_id, int $length = 24, string $type = self::MFA_TYPE_ALPHANUMERIC): array
    {

        $existing = null;

        try {
            $existing = $this->getPasswordRequest($user_id);
        } catch (DoesNotExistException) {
            // Do nothing;
        }

        if (is_array($existing)) { // Check wait time

            $now = time();
            $mfa_wait = (int)$this->rbacService->getConfig('user.mfa.wait', 3);

            if (isset($existing['created_at']) && $mfa_wait > 0) {

                if ((int)$existing['created_at'] > $now - ($mfa_wait * 60)) {
                    throw new AlreadyExistsException('Unable to create password request: Wait time not yet elapsed');
                }

            }

        }

        $mfa = $this->createMfaArray($length, $type);

        try {

            $this->withProtectedPrefix()->upsert([
                'user' => $user_id,
                'meta_key' => $this->getProtectedPrefix() . 'password_request',
                'meta_value' => json_encode([
                    'created_at' => Arr::get($mfa, 'created_at', 0),
                    'expires_at' => Arr::get($mfa, 'expires_at', time()),
                    'value' => $this->rbacService->createHash(Arr::get($mfa, 'value', ''))
                ])
            ]);

        } catch (OrmServiceException) {
            throw new UnexpectedException('Unable to create password request: Error saving request');
        }

        $this->rbacService->ormService->events->doEvent('rbac.user.password.request', $user_id, $mfa);

        return $mfa;

    }

    /**
     * Get non-deleted password request, or quietly delete if invalid or expired.
     * Value can be verified using RbacService->hashMatches().
     *
     * @param string $user_id
     * @return array (Keys: created_at, expires_at, value)
     * @throws DoesNotExistException
     */
    public function getPasswordRequest(string $user_id): array
    {

        $deleted_at_field = $this->getDeletedAtField();

        $request = $this->ormService->db->single("SELECT meta_value FROM $this->table_name WHERE user = :userId AND meta_key = :metaKey AND $deleted_at_field IS NULL", [
            'userId' => $user_id,
            'metaKey' => $this->getProtectedPrefix() . 'password_request'
        ]);

        if (!$request) {
            throw new DoesNotExistException('Unable to get password request: Password request does not exist');
        }

        if (!$this->mfaIsValid($request)) {
            $this->deletePasswordRequest($user_id);
            throw new DoesNotExistException('Unable to get password request: Password request invalid or expired');
        }

        return $request;

    }

    /**
     * Quietly hard-delete password request, if existing.
     *
     * @param string $user_id
     * @return bool
     */
    public function deletePasswordRequest(string $user_id): bool
    {
        return $this->ormService->db->delete($this->table_name, [
            'user' => $user_id,
            'meta_key' => $this->getProtectedPrefix() . 'password_request'
        ]);
    }

    /**
     * Quietly hard-delete all expired password requests.
     *
     * @return void
     */
    public function deleteExpiredPasswordRequests(): void
    {

        $table = $this->getTableName();

        $requests = $this->rbacService->ormService->db->select("SELECT id, meta_value FROM $table WHERE meta_key = :metaKey", [
            'metaKey' => $this->getProtectedPrefix() . 'password_request'
        ]);

        $delete_ids = [];

        foreach ($requests as $request) {

            if (!$this->mfaIsValid($request['meta_value'])) {
                $delete_ids[] = $request['id'];
            }

        }

        if (!empty($delete_ids)) {
            $this->rbacService->ormService->db->query("DELETE FROM $table WHERE id IN (" . implode(',', $delete_ids) . ")");
        }

    }

}