<?php

namespace Bayfront\BonesService\Rbac\Commands;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Exceptions\ServiceException;
use Bayfront\BonesService\Rbac\Models\PermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantPermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantRolePermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantRolesModel;
use Bayfront\BonesService\Rbac\Models\TenantsModel;
use Bayfront\BonesService\Rbac\Models\TenantTeamsModel;
use Bayfront\BonesService\Rbac\Models\TenantUserMetaModel;
use Bayfront\BonesService\Rbac\Models\TenantUserRolesModel;
use Bayfront\BonesService\Rbac\Models\TenantUsersModel;
use Bayfront\BonesService\Rbac\Models\TenantUserTeamsModel;
use Bayfront\BonesService\Rbac\Models\UserKeysModel;
use Bayfront\BonesService\Rbac\Models\UsersModel;
use Bayfront\BonesService\Rbac\RbacService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RbacSeed extends Command
{

    private RbacService $rbacService;

    /**
     * The container will resolve any dependencies.
     */
    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {

        $this->setName('rbac:seed')
            ->setDescription('Seed the RBAC service tables')
            ->addOption('force', null, InputOption::VALUE_NONE);

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        if (!$input->getOption('force')) {

            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('This action will update the database. Are you sure you wish to continue with this action? [y/n]', false);

            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            if (!$helper->ask($input, $output, $question)) {

                $output->writeln('<info>RBAC service seeding aborted!</info>');
                return Command::SUCCESS;
            }

        }

        $output->writeln('Seeding the RBAC service tables...');

        try {

            $permissions = new PermissionsModel($this->rbacService);
            $tenantPermissions = new TenantPermissionsModel($this->rbacService);
            $tenantRolePermissions = new TenantRolePermissionsModel($this->rbacService);
            $tenantRoles = new TenantRolesModel($this->rbacService);
            $tenants = new TenantsModel($this->rbacService);
            $tenantTeams = new TenantTeamsModel($this->rbacService);
            $tenantUserMeta = new TenantUserMetaModel($this->rbacService);
            $tenantUserRoles = new TenantUserRolesModel($this->rbacService);
            $tenantUsers = new TenantUsersModel($this->rbacService);
            $tenantUserTeams = new TenantUserTeamsModel($this->rbacService);
            $userKeys = new UserKeysModel($this->rbacService);
            $users = new UsersModel($this->rbacService);

            if ($users->getCount() > 0) {

                $output->writeln('<info>Users already exist. RBAC service seeding aborted!</info>');
                return Command::SUCCESS;

            }

            $password = 'password';

            $user_owner = $users->create([
                'email' => 'owner@example.com',
                'password' => $password,
                'meta' => [
                    'name_first' => 'Jon',
                    'name_last' => 'Doe'
                ],
                'enabled' => true,
                'admin' => false
            ]);

            $user_employee = $users->create([
                'email' => 'employee@example.com',
                'password' => $password,
                'meta' => [
                    'name_first' => 'Jane',
                    'name_last' => 'Doe'
                ],
                'enabled' => true,
                'admin' => false
            ]);

            $user_user = $users->create([
                'email' => 'user@example.com',
                'password' => $password,
                'meta' => [
                    'name_first' => 'Jill',
                    'name_last' => 'Doe'
                ],
                'enabled' => true,
                'admin' => false
            ]);

            $users->verify('owner@example.com');
            $users->verify('employee@example.com');
            $users->verify('user@example.com');

            $user_key = $userKeys->create([
                'user' => $user_owner->getPrimaryKey(),
                'name' => 'Example key'
            ]);

            $tenant = $tenants->create([
                'owner' => $user_owner->getPrimaryKey(),
                'domain' => 'example-organization',
                'name' => 'Example Organization',
                'enabled' => true
            ]);

            $p_create = $permissions->create([
                'name' => 'records.create',
                'description' => 'Create new records'
            ]);

            $p_read = $permissions->create([
                'name' => 'records.read',
                'description' => 'Read records'
            ]);

            $p_update = $permissions->create([
                'name' => 'records.update',
                'description' => 'Update records'
            ]);

            $p_delete = $permissions->create([
                'name' => 'records.delete',
                'description' => 'Delete records'
            ]);

            $tp_create = $tenantPermissions->create([
                'tenant' => $tenant->getPrimaryKey(),
                'permission' => $p_create->getPrimaryKey()
            ]);

            $tp_read = $tenantPermissions->create([
                'tenant' => $tenant->getPrimaryKey(),
                'permission' => $p_read->getPrimaryKey()
            ]);

            $tp_update = $tenantPermissions->create([
                'tenant' => $tenant->getPrimaryKey(),
                'permission' => $p_update->getPrimaryKey()
            ]);

            $tp_delete = $tenantPermissions->create([
                'tenant' => $tenant->getPrimaryKey(),
                'permission' => $p_delete->getPrimaryKey()
            ]);

            $role_admin = $tenantRoles->create([
                'tenant' => $tenant->getPrimaryKey(),
                'name' => 'Administrator',
                'description' => 'Administrative privileges'
            ]);

            $role_read_only = $tenantRoles->create([
                'tenant' => $tenant->getPrimaryKey(),
                'name' => 'Read only',
                'description' => 'Read only privileges'
            ]);

            $tenantRolePermissions->create([
                'role' => $role_admin->getPrimaryKey(),
                'tenant_permission' => $tp_create->getPrimaryKey()
            ]);

            $tenantRolePermissions->create([
                'role' => $role_admin->getPrimaryKey(),
                'tenant_permission' => $tp_read->getPrimaryKey()
            ]);

            $tenantRolePermissions->create([
                'role' => $role_admin->getPrimaryKey(),
                'tenant_permission' => $tp_update->getPrimaryKey()
            ]);

            $tenantRolePermissions->create([
                'role' => $role_admin->getPrimaryKey(),
                'tenant_permission' => $tp_delete->getPrimaryKey()
            ]);

            $tenantRolePermissions->create([
                'role' => $role_read_only->getPrimaryKey(),
                'tenant_permission' => $tp_read->getPrimaryKey()
            ]);

            $tu_employee = $tenantUsers->create([
                'tenant' => $tenant->getPrimaryKey(),
                'user' => $user_employee->getPrimaryKey(),
            ]);

            $tenantUserRoles->create([
                'tenant_user' => $tu_employee->getPrimaryKey(),
                'role' => $role_read_only->getPrimaryKey(),
            ]);

            $tu_user = $tenantUsers->create([
                'tenant' => $tenant->getPrimaryKey(),
                'user' => $user_user->getPrimaryKey(),
            ]);

            $tenantUserRoles->create([
                'tenant_user' => $tu_user->getPrimaryKey(),
                'role' => $role_read_only->getPrimaryKey(),
            ]);

            $team = $tenantTeams->create([
                'tenant' => $tenant->getPrimaryKey(),
                'name' => 'Employees',
                'description' => 'All employees'
            ]);

            $tu_admin = $tenantUsers->findByUserId($tenant->getPrimaryKey(), $user_owner->getPrimaryKey());

            $tenantUserTeams->create([
                'team' => $team->getPrimaryKey(),
                'tenant_user' => $tu_admin->getPrimaryKey()
            ]);

            $tenantUserTeams->create([
                'team' => $team->getPrimaryKey(),
                'tenant_user' => $tu_employee->getPrimaryKey()
            ]);

            $tenantUserMeta->create([
                'tenant_user' => $tu_employee->getPrimaryKey(),
                'meta_key' => 'hire_date',
                'meta_value' => '2024-01-15'
            ]);

        } catch (ServiceException $e) {

            $output->writeLn('<error>Error seeding database:</error>');
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;

        }

        $output->writeLn('<info>User credentials:</info>');

        $table = new Table($output);

        $table
            ->setHeaders(['ID', 'Email', 'Password'])
            ->setRows([
                [$user_owner->getPrimaryKey(), $user_owner->get('email'), $password],
                [$user_employee->getPrimaryKey(), $user_employee->get('email'), $password],
                [$user_user->getPrimaryKey(), $user_user->get('email'), $password],
            ]);

        $table->render();

        $output->writeLn('<info>Owner user key:</info> ' . Arr::get($user_key->read(), 'key_value', ''));

        $output->writeLn('<info>Seeding complete!</info>');

        return Command::SUCCESS;

    }

}