<?php

namespace Bayfront\BonesService\Rbac\Abstracts;

use Bayfront\BonesService\Orm\Models\ResourceModel;
use Bayfront\BonesService\Rbac\RbacService;

abstract class RbacModel extends ResourceModel
{

    public RbacService $rbacService;

    public function __construct(RbacService $rbacService, string $table)
    {
        $this->rbacService = $rbacService;

        $this->table_name = $rbacService->getTableName($table);
        $this->default_limit = $rbacService->getConfig('model.' . $table . '.default_limit', $rbacService->ormService->getConfig('default_limit', 100));
        $this->max_limit = $rbacService->getConfig('model.' . $table . '.max_limit', $rbacService->ormService->getConfig('max_limit', -1));
        $this->max_related_depth = $rbacService->getConfig('model.' . $table . '.max_related_depth', $rbacService->ormService->getConfig('max_related_depth', 3));

        parent::__construct($rbacService->ormService);
    }

}