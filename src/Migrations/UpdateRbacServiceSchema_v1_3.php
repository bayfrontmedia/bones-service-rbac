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
    private string $table_user_tokens;
    private string $table_users;

    public function __construct(RbacService $rbacService)
    {

        $this->db = $rbacService->ormService->db;

        $this->table_tenants = $rbacService->getTableName($rbacService::TABLE_TENANTS);
        $this->table_user_tokens = $rbacService->getTableName($rbacService::TABLE_USER_TOKENS);
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

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_user_tokens (
            `id` char(36) NOT NULL,
            `user` char(36) NOT NULL,
            `type` varchar(255) NOT NULL,
            `expires` int unsigned NOT NULL,
            `ip` varchar(255) NULL NOT NULL,
            `meta` JSON NULL DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            PRIMARY KEY (`id`),
            INDEX ut_type (type),
            CONSTRAINT `fk_ut_user__u_id` FOREIGN KEY (`user`) REFERENCES $this->table_users (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {

        $this->db->query("ALTER TABLE $this->table_user_tokens ADD deleted_at datetime NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE $this->table_tenants ADD deleted_at datetime NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE $this->table_users ADD deleted_at datetime NULL DEFAULT NULL");

    }

}