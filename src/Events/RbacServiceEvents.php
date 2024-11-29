<?php

namespace Bayfront\BonesService\Rbac\Events;

use Bayfront\Bones\Abstracts\EventSubscriber;
use Bayfront\Bones\Application\Services\Events\EventSubscription;
use Bayfront\Bones\Interfaces\EventSubscriberInterface;
use Bayfront\BonesService\Rbac\Commands\RbacSeed;
use Bayfront\BonesService\Rbac\RbacService;
use Symfony\Component\Console\Application;

class RbacServiceEvents extends EventSubscriber implements EventSubscriberInterface
{

    private RbacService $rbacService;

    /**
     * The container will resolve any dependencies.
     */

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
            new EventSubscription('app.cli', [$this, 'addConsoleCommands'], 10)
        ];
    }

    public function addConsoleCommands(Application $application): void
    {
        $application->add(new RbacSeed($this->rbacService));
    }

}