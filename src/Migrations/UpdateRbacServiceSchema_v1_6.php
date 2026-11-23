<?php

namespace Bayfront\BonesService\Rbac\Migrations;

use Bayfront\Bones\Interfaces\MigrationInterface;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\SimplePdo\Db;

class UpdateRbacServiceSchema_v1_6 implements MigrationInterface
{

    private Db $db;

    // Database tables

    private string $table_permissions;
    private string $table_tenant_invitations;
    private string $table_tenant_meta;
    private string $table_tenant_role_permissions;
    private string $table_tenant_permissions;
    private string $table_tenant_user_meta;
    private string $table_tenant_user_roles;
    private string $table_tenant_user_teams;
    private string $table_tenant_users;
    private string $table_tenants;
    private string $table_user_keys;
    private string $table_user_meta;
    private string $table_user_tokens;
    private string $table_users;

    public function __construct(RbacService $rbacService)
    {

        $this->db = $rbacService->ormService->db;

        $this->table_permissions = $rbacService->getTableName($rbacService::TABLE_PERMISSIONS);
        $this->table_tenant_invitations = $rbacService->getTableName($rbacService::TABLE_TENANT_INVITATIONS);
        $this->table_tenant_meta = $rbacService->getTableName($rbacService::TABLE_TENANT_META);
        $this->table_tenant_role_permissions = $rbacService->getTableName($rbacService::TABLE_TENANT_ROLE_PERMISSIONS);
        $this->table_tenant_permissions = $rbacService->getTableName($rbacService::TABLE_TENANT_PERMISSIONS);
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
        return 'Update RBAC service schema (v1.6)';
    }

    /**
     * @inheritDoc
     */
    public function up(): void
    {

        // Add new indexes

        $this->db->query("ALTER TABLE $this->table_user_tokens ADD INDEX `idx_ut_expires` (`expires`), ALGORITHM=INPLACE, LOCK=NONE");

        // Drop redundant indexes

        $this->db->query("ALTER TABLE $this->table_tenant_invitations DROP INDEX `ti_tenant`, ALGORITHM=INPLACE, LOCK=NONE");
        $this->db->query("ALTER TABLE $this->table_user_tokens DROP INDEX `fk_ut_user__u_id`, ALGORITHM=INPLACE, LOCK=NONE");
        $this->db->query("ALTER TABLE $this->table_user_tokens DROP INDEX `ut_type`, ALGORITHM=INPLACE, LOCK=NONE");

        // Rename remaining indexes (metadata-only)

        $this->db->query("ALTER TABLE $this->table_permissions RENAME INDEX `name` TO `uq_p_name`");
        $this->db->query("ALTER TABLE $this->table_tenant_invitations RENAME INDEX `fk_ti_tenant__t_id` TO `idx_ti_tenant`, RENAME INDEX `fk_ti_role__tr_id` TO `idx_ti_role`");
        $this->db->query("ALTER TABLE $this->table_tenant_meta RENAME INDEX `tenant` TO `uq_tm_tenant__meta_key`");
        $this->db->query("ALTER TABLE $this->table_tenant_permissions RENAME INDEX `tenant` TO `uq_tp_tenant__permission`, RENAME INDEX `fk_tp_permission__p_id` TO `idx_tp_permission`");
        $this->db->query("ALTER TABLE $this->table_tenant_role_permissions RENAME INDEX `role` TO `uq_trp_role__tenant_permission`, RENAME INDEX `fk_trp_tenant_permission__tp_id` TO `idx_trp_tenant_permission`");
        $this->db->query("ALTER TABLE $this->table_tenant_user_meta RENAME INDEX `tenant_user` TO `uq_tum_tenant_user__meta_key`");
        $this->db->query("ALTER TABLE $this->table_tenant_user_roles RENAME INDEX `tenant_user` TO `uq_tur_tenant_user__role`, RENAME INDEX `fk_tur_role__tr_id` TO `idx_tur_role`");
        $this->db->query("ALTER TABLE $this->table_tenant_user_teams RENAME INDEX `tenant_user` TO `uq_tut_tenant_user__team`, RENAME INDEX `fk_tut_team__tt_id` TO `idx_tut_team`");
        $this->db->query("ALTER TABLE $this->table_tenant_users RENAME INDEX `tenant` TO `uq_tu_tenant__user`, RENAME INDEX `fk_tu_user__u_id` TO `idx_tu_user`");
        $this->db->query("ALTER TABLE $this->table_tenants RENAME INDEX `domain` TO `uq_t_domain`, RENAME INDEX `fk_t_owner__u_id` TO `idx_t_owner`");
        $this->db->query("ALTER TABLE $this->table_user_keys RENAME INDEX `key_value` TO `uq_uk_key_value`");
        $this->db->query("ALTER TABLE $this->table_user_meta RENAME INDEX `user` TO `uq_um_user__meta_key`");
        $this->db->query("ALTER TABLE $this->table_user_tokens RENAME INDEX `ut_user_type` TO `idx_ut_user__type`");
        $this->db->query("ALTER TABLE $this->table_users RENAME INDEX `email` TO `uq_u_email`");

        // Refresh statistics

        $this->db->query("ANALYZE TABLE $this->table_user_tokens");

    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {

        // Restore original index names (metadata-only)

        $this->db->query("ALTER TABLE $this->table_permissions RENAME INDEX `uq_p_name` TO `name`");
        $this->db->query("ALTER TABLE $this->table_tenant_invitations RENAME INDEX `idx_ti_tenant` TO `fk_ti_tenant__t_id`, RENAME INDEX `idx_ti_role` TO `fk_ti_role__tr_id`");
        $this->db->query("ALTER TABLE $this->table_tenant_meta RENAME INDEX `uq_tm_tenant__meta_key` TO `tenant`");
        $this->db->query("ALTER TABLE $this->table_tenant_permissions RENAME INDEX `uq_tp_tenant__permission` TO `tenant`, RENAME INDEX `idx_tp_permission` TO `fk_tp_permission__p_id`");
        $this->db->query("ALTER TABLE $this->table_tenant_role_permissions RENAME INDEX `uq_trp_role__tenant_permission` TO `role`, RENAME INDEX `idx_trp_tenant_permission` TO `fk_trp_tenant_permission__tp_id`");
        $this->db->query("ALTER TABLE $this->table_tenant_user_meta RENAME INDEX `uq_tum_tenant_user__meta_key` TO `tenant_user`");
        $this->db->query("ALTER TABLE $this->table_tenant_user_roles RENAME INDEX `uq_tur_tenant_user__role` TO `tenant_user`, RENAME INDEX `idx_tur_role` TO `fk_tur_role__tr_id`");
        $this->db->query("ALTER TABLE $this->table_tenant_user_teams RENAME INDEX `uq_tut_tenant_user__team` TO `tenant_user`, RENAME INDEX `idx_tut_team` TO `fk_tut_team__tt_id`");
        $this->db->query("ALTER TABLE $this->table_tenant_users RENAME INDEX `uq_tu_tenant__user` TO `tenant`, RENAME INDEX `idx_tu_user` TO `fk_tu_user__u_id`");
        $this->db->query("ALTER TABLE $this->table_tenants RENAME INDEX `uq_t_domain` TO `domain`, RENAME INDEX `idx_t_owner` TO `fk_t_owner__u_id`");
        $this->db->query("ALTER TABLE $this->table_user_keys RENAME INDEX `uq_uk_key_value` TO `key_value`");
        $this->db->query("ALTER TABLE $this->table_user_meta RENAME INDEX `uq_um_user__meta_key` TO `user`");
        $this->db->query("ALTER TABLE $this->table_user_tokens RENAME INDEX `idx_ut_user__type` TO `ut_user_type`");
        $this->db->query("ALTER TABLE $this->table_users RENAME INDEX `uq_u_email` TO `email`");

        // Re-create dropped indexes (before dropping the composites)

        $this->db->query("ALTER TABLE $this->table_tenant_invitations ADD INDEX `ti_tenant` (`tenant`), ALGORITHM=INPLACE, LOCK=NONE");
        $this->db->query("ALTER TABLE $this->table_user_tokens ADD INDEX `fk_ut_user__u_id` (`user`), ALGORITHM=INPLACE, LOCK=NONE");
        $this->db->query("ALTER TABLE $this->table_user_tokens ADD INDEX `ut_type` (`type`), ALGORITHM=INPLACE, LOCK=NONE");

        // Drop new indexes

        $this->db->query("ALTER TABLE $this->table_user_tokens DROP INDEX `idx_ut_expires`, ALGORITHM=INPLACE, LOCK=NONE");

    }

}