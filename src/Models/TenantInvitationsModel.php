<?php

namespace Bayfront\BonesService\Rbac\Models;

use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Orm\Traits\Prunable;
use Bayfront\BonesService\Orm\Traits\SoftDeletes;
use Bayfront\BonesService\Rbac\Abstracts\RbacModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\SimplePdo\Query;
use Bayfront\TimeHelpers\Time;

class TenantInvitationsModel extends RbacModel
{

    use Prunable, SoftDeletes;

    /**
     * The container will resolve any dependencies.
     * OrmService and Db are required by the abstract model.
     *
     * @param RbacService $rbacService
     */

    public function __construct(RbacService $rbacService)
    {
        parent::__construct($rbacService, $rbacService::TABLE_TENANT_INVITATIONS);
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
        'role' => TenantRolesModel::class
    ];

    /**
     * Fields which are required when creating resource.
     *
     * @var array
     */
    protected array $required_fields = [
        'email',
        'tenant',
        'role'
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
        'tenant' => 'isString|lengthEquals:36',
        'role' => 'isString|lengthEquals:36'
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
            'email',
            'tenant'
        ]
    ];

    /**
     * Fields which can be read from the resource.
     *
     * @var array
     */
    protected array $allowed_fields_read = [
        'id',
        'email',
        'tenant',
        'role',
        'expires_at',
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
        'email',
        'tenant',
        'role'
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
     * - Define expires_at
     *
     * @param array $fields
     * @return array
     */
    protected function onCreating(array $fields): array
    {
        $fields['id'] = $this->createUuid();
        $fields['expires_at'] = Time::getDateTime(time() + ($this->rbacService->getConfig('invitation_duration', 0) * 60));
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
        $this->ormService->events->doEvent('rbac.tenant.invitation.created', $resource);
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
     * Trait: Prunable
     *
     * @inheritDoc
     */
    protected function getPruneField(): string
    {
        return 'expires_at';
    }

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
     * Find tenant invitation by email and tenant ID.
     *
     * Can be used with the SoftDeletes trait trashed filters.
     *
     * @param string $email
     * @param string $tenant_id
     * @return OrmResource
     * @throws DoesNotExistException
     * @throws UnexpectedException
     */
    public function findByEmail(string $email, string $tenant_id): OrmResource
    {

        $invitation_id = $this->ormService->db->single("SELECT id FROM $this->table_name WHERE email = :email AND tenant = :tenant", [
            'email' => $email,
            'tenant' => $tenant_id
        ]);

        if (!$invitation_id) {
            throw new DoesNotExistException('Unable to find tenant invitation: Invitation does not exist');
        }

        return $this->find($invitation_id);

    }

    /**
     * Accept invitation.
     *
     * @param array $invitation (Keys: id, email, tenant, role, expires_at)
     * @return void
     * @throws DoesNotExistException
     * @throws InvalidFieldException
     * @throws UnexpectedException
     */
    private function acceptInvitation(array $invitation): void
    {

        // Delete if expired

        if (Time::inPast($invitation['expires_at'])) {

            $this->delete($invitation['id']);
            throw new DoesNotExistException('Unable to verify tenant invitation: Invitation is expired');

        }

        // Check user exists with email

        $usersModel = new UsersModel($this->rbacService);

        $user = $usersModel->findByEmail($invitation['email']);

        // Add user to tenant

        $tenantUsersModel = new TenantUsersModel($this->rbacService);

        try {

            $tu = $tenantUsersModel->create([
                'tenant' => $invitation['tenant'],
                'user' => $user->getPrimaryKey()
            ]);

        } catch (AlreadyExistsException) { // User has already been added to tenant

            $tu = $tenantUsersModel->findByUserId($invitation['tenant'], $user->getPrimaryKey());

        }

        // Add tenant user to role

        $tenantUserRolesModel = new TenantUserRolesModel($this->rbacService);

        try {

            $tenantUserRolesModel->create([
                'tenant_user' => $tu->getPrimaryKey(),
                'role' => $invitation['role']
            ]);

        } catch (AlreadyExistsException) {
            // User already has role. Do nothing
        }

        // Delete invitation

        $this->hardDelete($invitation['id']);

        $this->ormService->events->doEvent('rbac.tenant.invitation.accepted', $user, $invitation['tenant']);

    }

    /**
     * Accept tenant invitation using invitation ID.
     *
     * Adds non-deleted user to tenant with invited role and hard-deletes invitation.
     * The rbac.tenant.invitation.accepted event is executed on success.
     *
     * @param string $invitation_id
     * @return void
     * @throws DoesNotExistException
     * @throws InvalidFieldException
     * @throws UnexpectedException
     */
    public function acceptFromId(string $invitation_id): void
    {

        // Get invitation

        $deleted_at_field = $this->getDeletedAtField();

        $invitation = $this->ormService->db->row("SELECT id, email, tenant, role, expires_at FROM $this->table_name WHERE id = :id AND $deleted_at_field IS NULL", [
            'id' => $invitation_id
        ]);

        if (!$invitation) {
            throw new DoesNotExistException('Unable to verify tenant invitation: Invitation does not exist');
        }

        $this->acceptInvitation($invitation);

    }

    /**
     * Accept tenant invitation using email and tenant ID.
     *
     * Adds non-deleted user to tenant with invited role and hard-deletes invitation.
     * The rbac.tenant.invitation.accepted event is executed on success.
     *
     * @param string $email
     * @param string $tenant_id
     * @return void
     * @throws DoesNotExistException
     * @throws InvalidFieldException
     * @throws UnexpectedException
     */
    public function acceptFromEmail(string $email, string $tenant_id): void
    {

        // Get invitation

        $deleted_at_field = $this->getDeletedAtField();

        $invitation = $this->ormService->db->row("SELECT id, email, tenant, role, expires_at FROM $this->table_name WHERE email = :email AND tenant = :tenant AND $deleted_at_field IS NULL", [
            'email' => $email,
            'tenant' => $tenant_id
        ]);

        if (!$invitation) {
            throw new DoesNotExistException('Unable to verify tenant invitation: Invitation does not exist');
        }

        $this->acceptInvitation($invitation);

    }

}