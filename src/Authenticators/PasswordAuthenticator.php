<?php

namespace Bayfront\BonesService\Rbac\Authenticators;

use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Orm\Exceptions\OrmServiceException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidPasswordException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException;
use Bayfront\BonesService\Rbac\Models\UsersModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\BonesService\Rbac\User;

class PasswordAuthenticator
{

    public RbacService $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    /**
     * Authenticate with email and password.
     *
     * @param string $email
     * @param string $password
     * @return User
     * @throws InvalidPasswordException
     * @throws UnexpectedAuthenticationException
     * @throws UserDisabledException
     * @throws UserDoesNotExistException
     * @throws UserNotVerifiedException
     */
    public function authenticate(string $email, string $password): User
    {

        $usersModel = new UsersModel($this->rbacService);

        $users_table = $usersModel->getTableName();

        $results = $this->rbacService->ormService->db->row("SELECT id, password, salt FROM $users_table WHERE email = :email", [
            'email' => $email
        ]);

        // User exists

        if (!$results) {
            throw new UserDoesNotExistException('Unable to authenticate user: User does not exist');
        }

        // Password is valid

        if (!App::isPasswordHashValid($password, $results['salt'], $results['password'])) {
            throw new InvalidPasswordException('Unable to authenticate user: Incorrect password');
        }

        try {
            $user_resource = $usersModel->find($results['id']);
        } catch (OrmServiceException) {
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

        return $user;

    }

}