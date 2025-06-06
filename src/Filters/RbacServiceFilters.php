<?php

namespace Bayfront\BonesService\Rbac\Filters;

use Bayfront\Bones\Abstracts\FilterSubscriber;
use Bayfront\Bones\Application\Services\Filters\FilterSubscription;
use Bayfront\Bones\Interfaces\FilterSubscriberInterface;
use Bayfront\BonesService\Rbac\Migrations\CreateRbacServiceSchema;
use Bayfront\BonesService\Rbac\Migrations\UpdateRbacServiceSchema_v1_1;
use Bayfront\BonesService\Rbac\Migrations\UpdateRbacServiceSchema_v1_3;
use Bayfront\BonesService\Rbac\RbacService;

class RbacServiceFilters extends FilterSubscriber implements FilterSubscriberInterface
{

    private RbacService $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptions(): array
    {
        return [
            new FilterSubscription('bones.migrations', [$this, 'createRbacServiceSchema'], 10)
        ];
    }

    public function createRbacServiceSchema(array $array): array
    {
        return array_merge($array, [
            new CreateRbacServiceSchema($this->rbacService),
            new UpdateRbacServiceSchema_v1_1($this->rbacService),
            new UpdateRbacServiceSchema_v1_3($this->rbacService)
        ]);
    }

}