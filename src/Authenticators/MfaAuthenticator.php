<?php

namespace Bayfront\BonesService\Rbac\Authenticators;

use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\MfaDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException;
use Bayfront\BonesService\Rbac\Models\UsersModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\BonesService\Rbac\User;

class MfaAuthenticator
{

    public RbacService $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    /**
     * Authenticate with MFA, quietly deleting if expired or when authenticated.
     *
     * @param string $email
     * @param string $mfa_value
     * @return User
     * @throws MfaDoesNotExistException
     * @throws UnexpectedAuthenticationException
     * @throws UserDisabledException
     * @throws UserDoesNotExistException
     * @throws UserNotVerifiedException
     */
    public function authenticate(string $email, string $mfa_value): User
    {

        // ------------------------- MFA -------------------------

        $usersModel = new UsersModel($this->rbacService);

        if (!$usersModel->mfaIsValid($email, $mfa_value)) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.mfa', $email);
            throw new MfaDoesNotExistException('Unable to authenticate user: MFA does not exist');
        }

        // ------------------------- User -------------------------

        try {
            $user_resource = $usersModel->findByEmail($email);
        } catch (DoesNotExistException) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.mfa', $email);
            throw new UserDoesNotExistException('Unable to authenticate user: User does not exist');
        } catch (UnexpectedException) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.mfa', $email);
            throw new UnexpectedAuthenticationException('Unable to authenticate user: Unable to find user');
        }

        $user = new User($this->rbacService, $user_resource);

        // User is enabled

        if (!$user->isEnabled()) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.mfa', $email);
            throw new UserDisabledException('Unable to authenticate user: User is disabled');
        }

        // Check user verification

        if ($this->rbacService->getConfig('user.require_verification', true) === true
            && $user->get('verified_at') === null) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.mfa', $email);
            throw new UserNotVerifiedException('Unable to authenticate user: User is not verified');
        }

        // Delete mfa

        $usersModel->deleteMfa($email);

        $this->rbacService->ormService->events->doEvent('rbac.auth.success', $user);

        return $user;

    }

}