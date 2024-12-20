<?php

namespace Bayfront\BonesService\Rbac\Authenticators;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\ExpiredUserKeyException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidDomainException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidIpException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidUserKeyException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException;
use Bayfront\BonesService\Rbac\Models\UserKeysModel;
use Bayfront\BonesService\Rbac\Models\UsersModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\BonesService\Rbac\User;
use Bayfront\TimeHelpers\Time;

class UserKeyAuthenticator
{

    public RbacService $rbacService;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    /**
     * Authenticate with user key.
     *
     * @param string $user_key
     * @param string $ip (Client IP address)
     * @param string $domain (Client referring domain)
     * @return User
     * @throws ExpiredUserKeyException
     * @throws InvalidDomainException
     * @throws InvalidIpException
     * @throws InvalidUserKeyException
     * @throws UnexpectedAuthenticationException
     * @throws UserDisabledException
     * @throws UserDoesNotExistException
     * @throws UserNotVerifiedException
     */
    public function authenticate(string $user_key, string $ip = '', string $domain = ''): User
    {

        // ------------------------- User key -------------------------

        $userKeysModel = new UserKeysModel($this->rbacService);

        // User key exists

        try {
            $user_key_resource = $userKeysModel->findByKey($user_key);
        } catch (DoesNotExistException) {
            throw new InvalidUserKeyException('Unable to authenticate user: User key does not exist');
        } catch (UnexpectedException) {
            throw new UnexpectedAuthenticationException('Unable to authenticate user: Unexpected error');
        }

        $user_key = $user_key_resource->read();

        /*
         * Not expired
         *
         * Will not automatically delete to allow user to reference expired token.
         * These can be bulk pruned/deleted by the application as needed.
         */

        if (Time::inPast(Arr::get($user_key, 'expires_at', Time::getDateTime()))) {
            throw new ExpiredUserKeyException('Unable to authenticate user: User key is expired');
        }

        // Allowed domain

        $allowed_domains = Arr::get($user_key, 'allowed_domains');

        if (is_array($allowed_domains) && !empty($allowed_domains)) {

            if (!in_array($domain, $allowed_domains)) {
                throw new InvalidDomainException('Unable to authenticate user: Invalid domain');
            }

        }

        // Allowed IP

        $allowed_ips = Arr::get($user_key, 'allowed_ips');

        if (is_array($allowed_ips) && !empty($allowed_ips)) {

            if (!in_array($ip, $allowed_ips)) {
                throw new InvalidIpException('Unable to authenticate user: Invalid IP');
            }

        }

        // ------------------------- User -------------------------

        $usersModel = new UsersModel($this->rbacService);

        try {
            $user_resource = $usersModel->find(Arr::get($user_key, 'user', ''));
        } catch (DoesNotExistException) { // User key exists, but user is soft-deleted
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

        // Update key

        /*
         * Do not check for successful update.
         * If more than one authentication request is made per second, the last_used date
         * will not change, and an update will not take place.
         */

        $this->rbacService->ormService->db->update($userKeysModel->getTableName(), [
            'last_used' => Time::getDateTime()
        ], [
            'id' => Arr::get($user_key, 'id', '')
        ]);

        return $user;

    }

}