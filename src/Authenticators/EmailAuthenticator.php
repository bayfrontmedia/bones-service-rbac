<?php

namespace Bayfront\BonesService\Rbac\Authenticators;

use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException;
use Bayfront\BonesService\Rbac\Models\UsersModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\BonesService\Rbac\User;

class EmailAuthenticator
{

    public RbacService $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    /**
     * Authenticate with email.
     *
     * NOTE:
     * This should be used in conjunction with another authentication method,
     * such as an MFA.
     * Because of this, the rbac.auth.success event is not executed.
     *
     * @param string $email
     * @return User
     * @throws UnexpectedAuthenticationException
     * @throws UserDisabledException
     * @throws UserDoesNotExistException
     * @throws UserNotVerifiedException
     */
    public function authenticate(string $email): User
    {

        $usersModel = new UsersModel($this->rbacService);

        try {
            $user_resource = $usersModel->findByEmail($email);
        } catch (DoesNotExistException) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.email', $email);
            throw new UserDoesNotExistException('Unable to authenticate user: User does not exist');
        } catch (UnexpectedException) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.email', $email);
            throw new UnexpectedAuthenticationException('Unable to authenticate user: Unable to find user');
        }

        $user = new User($this->rbacService, $user_resource);

        // User is enabled

        if (!$user->isEnabled()) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.email', $email);
            throw new UserDisabledException('Unable to authenticate user: User is disabled');
        }

        // Check user verification

        if ($this->rbacService->getConfig('user.require_verification', true) === true
            && $user->get('verified_at') === null) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.email', $email);
            throw new UserNotVerifiedException('Unable to authenticate user: User is not verified');
        }

        return $user;

    }

}