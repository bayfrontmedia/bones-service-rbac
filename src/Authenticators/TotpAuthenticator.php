<?php

namespace Bayfront\BonesService\Rbac\Authenticators;

use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\TotpDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException;
use Bayfront\BonesService\Rbac\Models\UserMetaModel;
use Bayfront\BonesService\Rbac\Models\UsersModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\BonesService\Rbac\User;

class TotpAuthenticator
{

    public RbacService $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    /**
     * Authenticate with user TOTP, quietly deleting if expired or when authenticated.
     *
     * @param string $email
     * @param string $value
     * @return User
     * @throws TotpDoesNotExistException
     * @throws UnexpectedAuthenticationException
     * @throws UserDisabledException
     * @throws UserDoesNotExistException
     * @throws UserNotVerifiedException
     */
    public function authenticate(string $email, string $value): User
    {

        // ------------------------- User -------------------------

        $usersModel = new UsersModel($this->rbacService);

        try {
            $user_resource = $usersModel->findByEmail($email);
        } catch (DoesNotExistException) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.totp', $email);
            throw new UserDoesNotExistException('Unable to authenticate user: User does not exist');
        } catch (UnexpectedException) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.totp', $email);
            throw new UnexpectedAuthenticationException('Unable to authenticate user: Unable to find user');
        }

        $user = new User($this->rbacService, $user_resource);

        // User is enabled

        if (!$user->isEnabled()) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.totp', $email);
            throw new UserDisabledException('Unable to authenticate user: User is disabled');
        }

        // Check user verification

        if ($this->rbacService->getConfig('user.verification.require', true) === true
            && $user->get('verified_at') === null) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.totp', $email);
            throw new UserNotVerifiedException('Unable to authenticate user: User is not verified');
        }

        // ------------------------- TOTP -------------------------

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $totp = $userMetaModel->getUserTotp($user->getId());
        } catch (DoesNotExistException) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.totp', $email);
            throw new TotpDoesNotExistException('Unable to authenticate user: TOTP does not exist');
        }

        if (!$this->rbacService->hashMatches($totp->getValue(), $value)) {
            $this->rbacService->ormService->events->doEvent('rbac.auth.fail.totp', $email);
            throw new TotpDoesNotExistException('Unable to authenticate user: TOTP does not exist with value');
        }

        // Delete TOTP

        $userMetaModel->deleteUserTotp($user->getId());

        $this->rbacService->ormService->events->doEvent('rbac.auth.success', $user);

        return $user;

    }

}