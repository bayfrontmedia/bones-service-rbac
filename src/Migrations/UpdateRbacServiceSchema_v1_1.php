<?php

namespace Bayfront\BonesService\Rbac\Migrations;

use Bayfront\Bones\Interfaces\MigrationInterface;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\SimplePdo\Db;

class UpdateRbacServiceSchema_v1_1 implements MigrationInterface
{

    private Db $db;

    // Database tables

    private string $table_permissions;
    private string $table_tenant_invitations;
    private string $table_tenant_meta;
    private string $table_tenant_roles;
    private string $table_tenant_teams;
    private string $table_tenant_user_meta;
    private string $table_user_keys;
    private string $table_user_meta;

    public function __construct(RbacService $rbacService)
    {

        $this->db = $rbacService->ormService->db;

        $this->table_permissions = $rbacService->getTableName($rbacService::TABLE_PERMISSIONS);
        $this->table_tenant_invitations = $rbacService->getTableName($rbacService::TABLE_TENANT_INVITATIONS);
        $this->table_tenant_meta = $rbacService->getTableName($rbacService::TABLE_TENANT_META);
        $this->table_tenant_roles = $rbacService->getTableName($rbacService::TABLE_TENANT_ROLES);
        $this->table_tenant_teams = $rbacService->getTableName($rbacService::TABLE_TENANT_TEAMS);
        $this->table_tenant_user_meta = $rbacService->getTableName($rbacService::TABLE_TENANT_USER_META);
        $this->table_user_keys = $rbacService->getTableName($rbacService::TABLE_USER_KEYS);
        $this->table_user_meta = $rbacService->getTableName($rbacService::TABLE_USER_META);

    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'Update RBAC service schema (v1.1)';
    }

    /**
     * @inheritDoc
     */
    public function up(): void
    {

        $this->db->query("ALTER TABLE $this->table_permissions DROP COLUMN deleted_at");
        $this->db->query("ALTER TABLE $this->table_tenant_roles DROP COLUMN deleted_at");
        $this->db->query("ALTER TABLE $this->table_tenant_teams DROP COLUMN deleted_at");
        $this->db->query("ALTER TABLE $this->table_tenant_user_meta DROP COLUMN deleted_at");
        $this->db->query("ALTER TABLE $this->table_user_meta DROP COLUMN deleted_at");
        $this->db->query("ALTER TABLE $this->table_user_keys DROP COLUMN deleted_at");
        $this->db->query("ALTER TABLE $this->table_tenant_invitations DROP COLUMN deleted_at");
        $this->db->query("ALTER TABLE $this->table_tenant_meta DROP COLUMN deleted_at");

    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {

        $this->db->query("ALTER TABLE $this->table_tenant_meta ADD deleted_at datetime NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE $this->table_tenant_invitations ADD deleted_at datetime NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE $this->table_user_keys ADD deleted_at datetime NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE $this->table_user_meta ADD deleted_at datetime NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE $this->table_tenant_user_meta ADD deleted_at datetime NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE $this->table_tenant_teams ADD deleted_at datetime NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE $this->table_tenant_roles ADD deleted_at datetime NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE $this->table_permissions ADD deleted_at datetime NULL DEFAULT NULL");

    }

}