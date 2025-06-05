<?php

namespace Bayfront\BonesService\Rbac\Migrations;

use Bayfront\Bones\Interfaces\MigrationInterface;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\SimplePdo\Db;

class UpdateRbacServiceSchema_v1_3 implements MigrationInterface
{

    private Db $db;

    // Database tables

    private string $table_tenants;
    private string $table_users;

    public function __construct(RbacService $rbacService)
    {

        $this->db = $rbacService->ormService->db;

        $this->table_tenants = $rbacService->getTableName($rbacService::TABLE_TENANTS);
        $this->table_users = $rbacService->getTableName($rbacService::TABLE_USERS);

    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'Update RBAC service schema (v1.3)';
    }

    /**
     * @inheritDoc
     */
    public function up(): void
    {

        $this->db->query("ALTER TABLE $this->table_users DROP COLUMN deleted_at");
        $this->db->query("ALTER TABLE $this->table_tenants DROP COLUMN deleted_at");

    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {

        $this->db->query("ALTER TABLE $this->table_tenants ADD deleted_at datetime NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE $this->table_users ADD deleted_at datetime NULL DEFAULT NULL");

    }

}