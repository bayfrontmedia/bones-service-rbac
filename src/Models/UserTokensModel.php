<?php

namespace Bayfront\BonesService\Rbac\Models;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\OrmServiceException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Orm\Traits\Castable;
use Bayfront\BonesService\Orm\Traits\HasNullableJsonField;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\BonesService\Rbac\Abstracts\RbacModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\JWT\Jwt;
use Bayfront\JWT\TokenException;
use Bayfront\SimplePdo\Query;

class UserTokensModel extends RbacModel
{

    use Castable, HasNullableJsonField;

    /**
     * The container will resolve any dependencies.
     * OrmService and Db are required by the abstract model.
     *
     * @param RbacService $rbacService
     */

    public function __construct(RbacService $rbacService)
    {
        parent::__construct($rbacService, $rbacService::TABLE_USER_TOKENS);
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
        'type',
        'expires'
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
        'type' => 'isString|maxLength:255',
        'expires' => 'isInteger',
        'ip' => 'isString|maxLength:255',
        'meta' => 'isArray'
    ];

    /**
     * Unique fields whose values are checked on create/update.
     * The database is queried once for each key.
     *
     * Uniqueness of a single field as a string, or across multiple fields as an array.
     *
     * @var array
     */
    protected array $unique_fields = [];

    /**
     * Fields which can be read from the resource.
     *
     * @var array
     */
    protected array $allowed_fields_read = [
        'id',
        'user',
        'type',
        'expires',
        'ip',
        'meta',
        'created_at'
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
        'type',
        'ip',
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
     * @throws InvalidFieldException
     */
    protected function onCreating(array $fields): array
    {
        $fields['id'] = $this->createUuid();

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

    }

    /**
     * Filter query before reading resource(s).
     *
     * - Filter protected meta prefix
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

        $fields = $this->transform($fields, [
            'meta' => [$this, 'jsonDecode']
        ]);

        if (isset($fields['meta'])) {
            $meta = Arr::dot($fields['meta']);
            ksort($meta);
            $fields['meta'] = Arr::undot($meta);
        }

        return $fields;
    }

    /**
     * Filter fields before updating resource.
     *
     * @param OrmResource $existing
     * @param array $fields (Fields to update)
     * @return array
     * @throws InvalidFieldException
     */
    protected function onUpdating(OrmResource $existing, array $fields): array
    {

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

    }

    /**
     * Filter fields before writing to resource (creating and updating).
     *
     * - Filter protected meta prefix
     *
     * @param array $fields
     * @return array
     * @throws UnexpectedException
     */
    protected function onWriting(array $fields): array
    {
        return $this->transform($fields, [
            'meta' => [$this, 'jsonEncode']
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
     * - Filter protected meta prefix
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
     * - Reset protected meta prefix filters
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

    /*
     * |--------------------------------------------------------------------------
     * | Model-specific
     * |--------------------------------------------------------------------------
     */

    /**
     * Find user token by user ID and type.
     *
     * @param string $user_id
     * @param string $type
     * @return array
     * @throws InvalidRequestException
     * @throws UnexpectedException
     */
    public function readByType(string $user_id, string $type): array
    {

        $query = $this->list(new QueryParser([
            'fields' => '*',
            'filter' => [
                [
                    'user' => [
                        'eq' => $user_id
                    ]
                ],
                [
                    'type' => [
                        'eq' => $type
                    ]
                ]
            ]
        ]));

        return $query->list();

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

        $payload = array_merge($this->ormService->filters->doFilter('rbac.token.payload', []), [
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
     * @param string|null $ip (IP address which made the request)
     * @param array|null $meta (Metadata)
     * @return string
     * @throws UnexpectedException
     */
    public function createToken(string $user_id, string $type, ?string $ip = null, ?array $meta = []): string
    {

        $now = time();

        if ($type == self::TOKEN_TYPE_ACCESS) {

            $exp = $now + ($this->rbacService->getConfig('user.token.access_duration', 5) * 60);

            $jti = '';

            if ($this->rbacService->getConfig('user.token.revocable') === true) {

                try {

                    $token = $this->create([
                        'user' => $user_id,
                        'type' => $type,
                        'expires' => $exp,
                        'ip' => $ip,
                        'meta' => $meta
                    ]);

                } catch (OrmServiceException $e) {
                    throw new UnexpectedException('Unable to create access token: ' . $e->getMessage());
                }

                $jti = $token->getPrimaryKey();

            }

            return $this->createJwt($user_id, self::TOKEN_TYPE_ACCESS, $now, $exp, $jti);

        } else if ($type == self::TOKEN_TYPE_REFRESH) {

            $exp = $now + ($this->rbacService->getConfig('user.token.refresh_duration', 10080) * 60);

            try {

                $token = $this->create([
                    'user' => $user_id,
                    'type' => $type,
                    'expires' => $exp,
                    'ip' => $ip,
                    'meta' => $meta
                ]);

            } catch (OrmServiceException $e) {
                throw new UnexpectedException('Unable to create refresh token: ' . $e->getMessage());
            }

            return $this->createJwt($user_id, self::TOKEN_TYPE_REFRESH, $now, $exp, $token->getPrimaryKey());

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
     * Quietly delete token for user.
     *
     * @param string $user_id
     * @param string $type (TOKEN_TYPE_* constant)
     * @return bool
     */
    public function deleteToken(string $user_id, string $type): bool
    {

        $table = $this->getTableName();

        if ($type == self::TOKEN_TYPE_ACCESS) {

            return $this->ormService->db->query("DELETE FROM $table WHERE user = :user AND type = :accessToken", [
                'user' => $user_id,
                'accessToken' => self::TOKEN_TYPE_ACCESS
            ]);

        } else if ($type == self::TOKEN_TYPE_REFRESH) {

            return $this->ormService->db->query("DELETE FROM $table WHERE user = :user AND type = :refreshToken", [
                'user' => $user_id,
                'refreshToken' => self::TOKEN_TYPE_REFRESH
            ]);

        }

        return false;

    }

    /**
     * Quietly delete all tokens for user.
     *
     * @param string $user_id
     * @return bool
     */
    public function deleteAllTokens(string $user_id): bool
    {

        $table = $this->getTableName();

        return $this->ormService->db->query("DELETE FROM $table WHERE user = :user", [
            'user' => $user_id
        ]);

    }

    /**
     * Quietly delete all expired tokens.
     *
     * @return void
     */
    public function deleteExpiredTokens(): void
    {

        $table = $this->getTableName();

        $this->ormService->db->query("DELETE FROM $table WHERE expires < :now", [
            'now' => time()
        ]);


    }

}