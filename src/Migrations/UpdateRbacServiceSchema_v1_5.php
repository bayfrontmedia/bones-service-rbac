<?php

namespace Bayfront\BonesService\Rbac\Migrations;

use Bayfront\Bones\Interfaces\MigrationInterface;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\SimplePdo\Db;

class UpdateRbacServiceSchema_v1_5 implements MigrationInterface
{

    private Db $db;

    // Database tables

    private string $table_permissions;
    private string $table_tenant_invitations;
    private string $table_tenant_meta;
    private string $table_tenant_permissions;
    private string $table_tenant_role_permissions;
    private string $table_tenant_roles;
    private string $table_tenant_teams;
    private string $table_tenant_user_teams;
    private string $table_tenant_user_meta;

    private string $table_tenant_user_roles;
    private string $table_tenant_users;
    private string $table_tenants;
    private string $table_user_keys;

    private string $table_user_meta;
    private string $table_users;

    public function __construct(RbacService $rbacService)
    {

        $this->db = $rbacService->ormService->db;

        $this->table_permissions = $rbacService->getTableName($rbacService::TABLE_PERMISSIONS);
        $this->table_tenant_invitations = $rbacService->getTableName($rbacService::TABLE_TENANT_INVITATIONS);
        $this->table_tenant_meta = $rbacService->getTableName($rbacService::TABLE_TENANT_META);
        $this->table_tenant_permissions = $rbacService->getTableName($rbacService::TABLE_TENANT_PERMISSIONS);
        $this->table_tenant_role_permissions = $rbacService->getTableName($rbacService::TABLE_TENANT_ROLE_PERMISSIONS);
        $this->table_tenant_roles = $rbacService->getTableName($rbacService::TABLE_TENANT_ROLES);
        $this->table_tenant_teams = $rbacService->getTableName($rbacService::TABLE_TENANT_TEAMS);
        $this->table_tenant_user_meta = $rbacService->getTableName($rbacService::TABLE_TENANT_USER_META);
        $this->table_tenant_user_roles = $rbacService->getTableName($rbacService::TABLE_TENANT_USER_ROLES);
        $this->table_tenant_user_teams = $rbacService->getTableName($rbacService::TABLE_TENANT_USER_TEAMS);
        $this->table_tenant_users = $rbacService->getTableName($rbacService::TABLE_TENANT_USERS);
        $this->table_tenants = $rbacService->getTableName($rbacService::TABLE_TENANTS);
        $this->table_user_keys = $rbacService->getTableName($rbacService::TABLE_USER_KEYS);
        $this->table_user_meta = $rbacService->getTableName($rbacService::TABLE_USER_META);
        $this->table_user_tokens = $rbacService->getTableName($rbacService::TABLE_USER_TOKENS);
        $this->table_users = $rbacService->getTableName($rbacService::TABLE_USERS);

    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'Update RBAC service schema (v1.5)';
    }

    /**
     * @inheritDoc
     */
    public function up(): void
    {

        /*
         * The "id" column is already a globally unique UUID, so there is no need for the primary key to be (id, user).
         * This would also prevent efficient ID-only lookups.
         */

        $this->db->query("ALTER TABLE $this->table_user_keys DROP PRIMARY KEY, ADD PRIMARY KEY (`id`)");

        /*
         * Mistake in the v1.3 migration where the "ip" column should be nullable.
         */

        $this->db->query("ALTER TABLE $this->table_user_tokens MODIFY COLUMN `ip` varchar(255) NULL DEFAULT NULL");

        /*
         * Create indexes
         */

        $this->db->query("ALTER TABLE $this->table_tenant_invitations ADD INDEX ti_tenant (`tenant`)");

        $this->db->query("ALTER TABLE $this->table_user_tokens ADD INDEX ut_user_type (`user`, `type`)");

    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {

        $this->db->query("ALTER TABLE $this->table_user_tokens DROP INDEX ut_user_type");

        $this->db->query("ALTER TABLE $this->table_tenant_invitations DROP INDEX ti_tenant");

        //

        $this->db->query("ALTER TABLE $this->table_user_tokens MODIFY COLUMN `ip` varchar(255) NOT NULL");

        $this->db->query("ALTER TABLE $this->table_user_keys DROP PRIMARY KEY, ADD PRIMARY KEY (`id`, `user`)");

    }

}