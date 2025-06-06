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

class UserIdAuthenticator
{

    public RbacService $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    /**
     * Authenticate with user ID.
     *
     * NOTE:
     * Authentication is not secure with this method alone.
     *
     * @param mixed $user_id
     * @param bool $check_verified (Check if user is verified when require verification is enabled)
     * @return User
     * @throws UnexpectedAuthenticationException
     * @throws UserDisabledException
     * @throws UserDoesNotExistException
     * @throws UserNotVerifiedException
     */
    public function authenticate(mixed $user_id, bool $check_verified = true): User
    {

        $usersModel = new UsersModel($this->rbacService);

        try {
            $user_resource = $usersModel->find($user_id);
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

        if ($check_verified === true) {

            if ($this->rbacService->getConfig('user.require_verification', true) === true
                && $user->get('verified_at') === null) {
                throw new UserNotVerifiedException('Unable to authenticate user: User is not verified');
            }

        }

        return $user;

    }

}