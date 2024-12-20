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
            throw new UserDoesNotExistException('Unable to authenticate user: User does not exist');
        } catch (UnexpectedException) {
            throw new UnexpectedAuthenticationException('Unable to authenticate user: Unable to find user');
        }

        $user = new User($this->rbacService, $user_resource);

        // User is enabled

        if (!$user->isEnabled()) {
            throw new UserDisabledException('Unable to authenticate user: User is disabled');
        }

        // Check user verification

        if ($this->rbacService->getConfig('user.require_verification', true) === true
            && $user->get('verified_at') === null) {
            throw new UserNotVerifiedException('Unable to authenticate user: User is not verified');
        }

        // ------------------------- TOTP -------------------------

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $totp = $userMetaModel->getTotp($user->getId(), $userMetaModel->totp_meta_key_tfa);
        } catch (DoesNotExistException) {
            throw new TotpDoesNotExistException('Unable to authenticate user: TOTP does not exist');
        }

        if (!$this->rbacService->hashMatches($totp->getValue(), $value)) {
            throw new TotpDoesNotExistException('Unable to authenticate user: TOTP does not exist with value');
        }

        // Delete TOTP

        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_tfa);

        return $user;

    }

}