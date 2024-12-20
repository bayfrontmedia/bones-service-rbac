<?php

namespace Bayfront\BonesService\Rbac\Authenticators;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidTokenException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\TokenDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException;
use Bayfront\BonesService\Rbac\Models\UserMetaModel;
use Bayfront\BonesService\Rbac\Models\UsersModel;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\BonesService\Rbac\User;
use Bayfront\JWT\Jwt;
use Bayfront\JWT\TokenException;
use Bayfront\Validator\Rules\IsJson;

class TokenAuthenticator
{

    public RbacService $rbacService;
    private UserMetaModel $userMetaModel;
    private UsersModel $usersModel;
    private Jwt $jwt;

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
        $this->userMetaModel = new UserMetaModel($rbacService);
        $this->usersModel = new UsersModel($rbacService);
        $this->jwt = new Jwt(App::getConfig('app.key'));
    }

    public const TOKEN_TYPE_ACCESS = 'access';
    public const TOKEN_TYPE_REFRESH = 'refresh';

    /**
     * Authenticate token.
     * Revokes tokens on errors as needed.
     *
     * @param string $token
     * @param string $type (TOKEN_TYPE_* constant)
     * @return string (User ID)
     * @throws InvalidTokenException
     * @throws TokenDoesNotExistException
     * @throws UnexpectedAuthenticationException
     */
    private function authenticateToken(string $token, string $type): string
    {

        /*
         * Validate the JWT has not been modified, even if it is expired
         */

        try {

            $this->jwt->validateSignature($token);

        } catch (TokenException) {
            throw new InvalidTokenException('Unable to authenticate token: Invalid token');
        }

        /*
         * Decode JWT
         */

        try {

            $decoded = $this->jwt->decode($token);

        } catch (TokenException) {
            throw new UnexpectedAuthenticationException('Unable to authenticate token: Unable to decode token');
        }

        $user_id = Arr::get($decoded, 'payload.sub', '');

        /*
         * Validate type
         */

        if (Arr::get($decoded, 'payload.type') !== $type) {
            throw new InvalidTokenException('Unable to authenticate token: Invalid token type');
        }

        /*
         * Validate:
         * - iat
         * - nbf
         * - exp
         */

        try {

            $this->jwt->validateClaims($token);

        } catch (TokenException) { // Token expired

            $this->userMetaModel->deleteToken($user_id, $type);

            throw new TokenDoesNotExistException('Unable to authenticate token: Invalid claims');

        }

        /*
         * Compare iat/exp with config setting
         * App configuration may have changed since the JWT was created
         */

        if ($type == self::TOKEN_TYPE_REFRESH) {
            $exp = Arr::get($decoded, 'payload.iat', 0) + ($this->rbacService->getConfig('user.token.refresh_duration', 10080) * 60);
        } else {
            $exp = Arr::get($decoded, 'payload.iat', 0) + ($this->rbacService->getConfig('user.token.access_duration', 5) * 60);
        }

        if ($exp < time()) {

            $this->userMetaModel->deleteToken($user_id, $type);

            throw new TokenDoesNotExistException('Unable to authenticate token: Token was expired');

        }

        /*
         * Claims are not validated when authenticating a refresh token since the token may be expired.
         * If the token was expired, it may have been removed from the database.
         */

        if ($type == self::TOKEN_TYPE_REFRESH
            || $this->rbacService->getConfig('user.token.revocable') === true) { // jti should be defined

            try {

                if ($type == self::TOKEN_TYPE_ACCESS) {
                    $meta_key = 'access_token';
                } else {
                    $meta_key = 'refresh_token';
                }

                /*
                 * Validate:
                 * - jti (user meta ID)
                 */

                $meta = $this->userMetaModel->withProtectedPrefix()->find(Arr::get($decoded, 'payload.jti', ''));

            } catch (DoesNotExistException) {
                throw new TokenDoesNotExistException('Unable to authenticate token: Token does not exist for user');
            } catch (UnexpectedException) {
                throw new UnexpectedAuthenticationException('Unable to authenticate token: Error finding token');
            }

            /*
             * Validate:
             * - Correct user meta has been retrieved
             * - sub (user ID)
             */

            if ($meta->get('meta_key') !== $this->userMetaModel->getProtectedPrefix() . $meta_key
                || $meta->get('user') !== Arr::get($decoded, 'payload.sub', '')) { // Default one to string so both non-existing do not match

                throw new UnexpectedAuthenticationException('Unable to authenticate token: Invalid key or jti');

            }

            $meta_value = $meta->get('meta_value');

            $validator = new IsJson($meta_value);

            if (!$validator->isValid()) {

                $this->userMetaModel->deleteToken($user_id, $type);

                throw new UnexpectedAuthenticationException('Unable to authenticate token: Invalid token format');

            }

            $meta_value = json_decode($meta_value, true);

            /*
             * Validate:
             * - exp
             */

            if (Arr::get($meta_value, 'exp') != Arr::get($decoded, 'payload.exp', '')) { // Default one to string so both non-existing do not match

                $this->userMetaModel->deleteToken($user_id, $type);

                throw new TokenDoesNotExistException('Unable to authenticate token: Invalid token expiration');

            }

        }

        return $user_id;

    }

    /**
     * Authenticate valid user.
     * Revokes tokens on errors as needed.
     *
     * @param string $user_id
     * @return User
     * @throws UnexpectedAuthenticationException
     * @throws UserDisabledException
     * @throws UserDoesNotExistException
     * @throws UserNotVerifiedException
     */
    private function authenticateUser(string $user_id): User
    {

        try {
            $user_resource = $this->usersModel->find($user_id);
        } catch (DoesNotExistException) { // Token key exists, but user is soft-deleted
            $this->userMetaModel->deleteAllTokens($user_id);
            throw new UserDoesNotExistException('Unable to authenticate token: User does not exist');
        } catch (UnexpectedException) {
            throw new UnexpectedAuthenticationException('Unable to authenticate token: Unable to find user');
        }

        $user = new User($this->rbacService, $user_resource);

        // User is enabled

        if (!$user->isEnabled()) {
            $this->userMetaModel->deleteAllTokens($user_id);
            throw new UserDisabledException('Unable to authenticate token: User is disabled');
        }

        // Check user verification

        if ($this->rbacService->getConfig('user.require_verification', true) === true
            && $user->get('verified_at') === null) {

            $this->userMetaModel->deleteAllTokens($user_id);
            throw new UserNotVerifiedException('Unable to authenticate token: User is not verified');

        }

        return $user;

    }

    /**
     * Authenticate with access or refresh token.
     * Revokes tokens on errors as needed.
     *
     * @param string $token (Token value)
     * @param string $type (Any TYPE_* constant)
     * @return User
     * @throws InvalidTokenException
     * @throws TokenDoesNotExistException
     * @throws UnexpectedAuthenticationException
     * @throws UserDisabledException
     * @throws UserDoesNotExistException
     * @throws UserNotVerifiedException
     */
    public function authenticate(string $token, string $type): User
    {
        $user_id = $this->authenticateToken($token, $type);
        return $this->authenticateUser($user_id);
    }

}