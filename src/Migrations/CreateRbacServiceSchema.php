<?php

namespace Bayfront\BonesService\Rbac\Migrations;

use Bayfront\Bones\Interfaces\MigrationInterface;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\SimplePdo\Db;

class CreateRbacServiceSchema implements MigrationInterface
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
        $this->table_users = $rbacService->getTableName($rbacService::TABLE_USERS);

    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'Create RBAC service schema (v1.0)';
    }

    /**
     * @inheritDoc
     */
    public function up(): void
    {

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_users (
            `id` char(36) NOT NULL,
            `email` varchar(255) NOT NULL,
            `password` varchar(255) NOT NULL,
            `salt` varchar(255) NOT NULL,
            `meta` JSON NULL DEFAULT NULL,
            `admin` tinyint(1) NOT NULL DEFAULT '0',
            `enabled` tinyint(1) NOT NULL DEFAULT '0',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            `verified_at` datetime NULL DEFAULT NULL,
            `deleted_at` datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE (`email`)) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_tenants (
            `id` char(36) NOT NULL,
            `owner` char(36) NULL DEFAULT NULL,
            `domain` varchar(63) NOT NULL,
            `name` varchar(255) NOT NULL,
            `meta` JSON NULL DEFAULT NULL,
            `enabled` tinyint(1) NOT NULL DEFAULT '0',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            `deleted_at` datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`), 
            UNIQUE (`domain`),
            CONSTRAINT `fk_t_owner__u_id` FOREIGN KEY (`owner`) REFERENCES $this->table_users (`id`) ON DELETE SET NULL)
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_tenant_users (
            `id` char(36) NOT NULL,
            `tenant` char(36) NOT NULL,
            `user` char(36) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            PRIMARY KEY (`id`),
            UNIQUE (`tenant`,`user`),
            CONSTRAINT `fk_tu_tenant__t_id` FOREIGN KEY (`tenant`) REFERENCES $this->table_tenants (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tu_user__u_id` FOREIGN KEY (`user`) REFERENCES $this->table_users (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_permissions (
            `id` char(36) NOT NULL,
            `name` varchar(255) NOT NULL,
            `description` varchar(255) NULL DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            `deleted_at` datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE (`name`))
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_tenant_permissions (
            `id` char(36) NOT NULL,
            `tenant` char(36) NOT NULL,
            `permission` char(36) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            PRIMARY KEY (`id`),
            UNIQUE (`tenant`,`permission`),
            CONSTRAINT `fk_tp_tenant__t_id` FOREIGN KEY (`tenant`) REFERENCES $this->table_tenants (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tp_permission__p_id` FOREIGN KEY (`permission`) REFERENCES $this->table_permissions (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_tenant_roles (
            `id` char(36) NOT NULL,
            `tenant` char(36) NOT NULL,
            `name` varchar(255) NOT NULL,
            `description` varchar(255) NULL DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            `deleted_at` datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE uq_tr_tenant__name(`tenant`,`name`),
            CONSTRAINT `fk_tr_tenant__t_id` FOREIGN KEY (`tenant`) REFERENCES $this->table_tenants (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_tenant_role_permissions (
            `id` char(36) NOT NULL,
            `role` char(36) NOT NULL,
            `tenant_permission` char(36) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            PRIMARY KEY (`id`),
            UNIQUE (`role`,`tenant_permission`),
            CONSTRAINT `fk_trp_role__tr_id` FOREIGN KEY (`role`) REFERENCES $this->table_tenant_roles (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_trp_tenant_permission__tp_id` FOREIGN KEY (`tenant_permission`) REFERENCES $this->table_tenant_permissions (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_tenant_user_roles (
            `id` char(36) NOT NULL,
            `tenant_user` char(36) NOT NULL,
            `role` char(36) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            PRIMARY KEY (`id`),
            UNIQUE (`tenant_user`,`role`),
            CONSTRAINT `fk_tur_tenant_user__tu_id` FOREIGN KEY (`tenant_user`) REFERENCES $this->table_tenant_users (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tur_role__tr_id` FOREIGN KEY (`role`) REFERENCES $this->table_tenant_roles (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_tenant_teams (
            `id` char(36) NOT NULL,
            `tenant` char(36) NOT NULL,
            `name` varchar(255) NOT NULL,
            `description` varchar(255) NULL DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            `deleted_at` datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE uq_tt_tenant__name(`tenant`,`name`),
            CONSTRAINT `fk_tt_tenant__t_id` FOREIGN KEY (`tenant`) REFERENCES $this->table_tenants (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_tenant_user_teams (
            `id` char(36) NOT NULL,
            `tenant_user` char(36) NOT NULL,
            `team` char(36) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            PRIMARY KEY (`id`),
            UNIQUE (`tenant_user`,`team`),
            CONSTRAINT `fk_tut_tenant_user__tu_id` FOREIGN KEY (`tenant_user`) REFERENCES $this->table_tenant_users (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tut_team__tt_id` FOREIGN KEY (`team`) REFERENCES $this->table_tenant_teams (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_tenant_user_meta (
            `id` char(36) NOT NULL,    
            `tenant_user` char(36) NOT NULL,
            `meta_key` varchar(255) NOT NULL,
            `meta_value` longtext DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            `deleted_at` datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE (`tenant_user`,`meta_key`),
            CONSTRAINT `fk_tum_tenant_user__tu_id` FOREIGN KEY (`tenant_user`) REFERENCES $this->table_tenant_users (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_user_meta (
            `id` char(36) NOT NULL,
            `user` char(36) NOT NULL,
            `meta_key` varchar(255) NOT NULL,
            `meta_value` longtext DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            `deleted_at` datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE (`user`,`meta_key`),
            CONSTRAINT `fk_um_user__u_id` FOREIGN KEY (`user`) REFERENCES $this->table_users (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_user_keys (
            `id` char(36) NOT NULL,
            `user` char(36) NOT NULL,
            `name` varchar(255) DEFAULT NULL,
            `key_value` binary(32),
            `allowed_domains` JSON NULL DEFAULT NULL,
            `allowed_ips` JSON NULL DEFAULT NULL,
            `expires_at` datetime NULL DEFAULT NULL,
            `last_used` datetime NULL DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            `deleted_at` datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`,`user`),
            UNIQUE uq_uk_user__name(`user`,`name`),
            UNIQUE (`key_value`),
            CONSTRAINT `fk_uk_user__u_id` FOREIGN KEY (`user`) REFERENCES $this->table_users (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_tenant_invitations (
            `id` char(36) NOT NULL,
            `email` varchar(255) NOT NULL,
            `tenant` char(36) NOT NULL,
            `role` char(36) NOT NULL,
            `expires_at` datetime NULL DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            `deleted_at` datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE uq_ti_email__tenant(`email`,`tenant`),
            CONSTRAINT `fk_ti_tenant__t_id` FOREIGN KEY (`tenant`) REFERENCES $this->table_tenants (`id`) ON DELETE CASCADE, 
            CONSTRAINT `fk_ti_role__tr_id` FOREIGN KEY (`role`) REFERENCES $this->table_tenant_roles (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS $this->table_tenant_meta (
            `id` char(36) NOT NULL,
            `tenant` char(36) NOT NULL,
            `meta_key` varchar(255) NOT NULL,
            `meta_value` longtext DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
            `deleted_at` datetime NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE (`tenant`,`meta_key`),
            CONSTRAINT `fk_tm_tenant__t_id` FOREIGN KEY (`tenant`) REFERENCES $this->table_tenants (`id`) ON DELETE CASCADE) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {

        $this->db->query("DROP TABLE IF EXISTS $this->table_tenant_meta");
        $this->db->query("DROP TABLE IF EXISTS $this->table_tenant_invitations");
        $this->db->query("DROP TABLE IF EXISTS $this->table_user_keys");
        $this->db->query("DROP TABLE IF EXISTS $this->table_user_meta");
        $this->db->query("DROP TABLE IF EXISTS $this->table_tenant_user_meta");
        $this->db->query("DROP TABLE IF EXISTS $this->table_tenant_user_teams");
        $this->db->query("DROP TABLE IF EXISTS $this->table_tenant_teams");
        $this->db->query("DROP TABLE IF EXISTS $this->table_tenant_user_roles");
        $this->db->query("DROP TABLE IF EXISTS $this->table_tenant_role_permissions");
        $this->db->query("DROP TABLE IF EXISTS $this->table_tenant_roles");
        $this->db->query("DROP TABLE IF EXISTS $this->table_tenant_permissions");
        $this->db->query("DROP TABLE IF EXISTS $this->table_permissions");
        $this->db->query("DROP TABLE IF EXISTS $this->table_tenant_users");
        $this->db->query("DROP TABLE IF EXISTS $this->table_tenants");
        $this->db->query("DROP TABLE IF EXISTS $this->table_users");

    }

}