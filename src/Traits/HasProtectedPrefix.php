<?php

namespace Bayfront\BonesService\Rbac\Traits;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\OrmServiceException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Rbac\Totp;
use Bayfront\SimplePdo\Exceptions\QueryException;
use Bayfront\SimplePdo\Query;
use Bayfront\StringHelpers\Str;
use Bayfront\Validator\Rules\IsJson;

/**
 * Necessary filters for protected prefix.
 */
trait HasProtectedPrefix
{

    private bool $with_protected = false;
    private bool $only_protected = false;

    private string $column_name = 'meta_key';

    /**
     * Set column name;
     *
     * @param string $column_name
     * @return void
     */
    protected function setColumnName(string $column_name): void
    {
        $this->column_name = $column_name;
    }

    /**
     * Get column name.
     *
     * @return string
     */
    protected function getColumnName(): string
    {
        return $this->column_name;
    }

    /**
     * Filter next query to include protected prefix.
     *
     * @return $this
     */
    public function withProtectedPrefix(): static
    {
        $this->with_protected = true;
        return $this;
    }

    /**
     * Filter next query to include only protected prefix.
     *
     * @return $this
     */
    public function onlyProtectedPrefix(): static
    {
        $this->only_protected = true;
        return $this;
    }

    /**
     * Get protected prefix.
     *
     * @return string
     */
    public function getProtectedPrefix(): string
    {
        return $this->rbacService->getConfig('protected_prefix', '_app-');
    }

    /**
     * Filter query to handle protected prefix.
     * Add to ResourceModel's onReading method.
     *
     * @param Query $query
     * @return Query
     * @throws UnexpectedException
     */
    protected function filterProtectedPrefixReading(Query $query): Query
    {

        if ($this->only_protected === true) {

            try {
                $query->where($this->getColumnName(), Query::OPERATOR_STARTS_WITH_INSENSITIVE, $this->getProtectedPrefix());
            } catch (QueryException) {
                throw new UnexpectedException('Unable to query: Error building query');
            }

        } else if ($this->with_protected === false) {

            try {
                $query->where($this->getColumnName(), Query::OPERATOR_DOES_NOT_START_WITH_INSENSITIVE, $this->getProtectedPrefix());
            } catch (QueryException) {
                throw new UnexpectedException('Unable to query: Error building query');
            }

        }

        return $query;

    }

    /**
     * Filter fields to handle protected prefix.
     * Add to ResourceModel's onWriting method.
     *
     * @param array $fields
     * @return array
     * @throws InvalidFieldException
     */
    protected function filterProtectedPrefixWriting(array $fields): array
    {

        if ($this->only_protected === true && !str_starts_with(Arr::get($fields, $this->getColumnName(), ''), $this->getProtectedPrefix())) {
            throw new InvalidFieldException('Unable to write: Column must be protected');
        } else if ($this->with_protected === false && str_starts_with(Arr::get($fields, $this->getColumnName(), ''), $this->getProtectedPrefix())) {
            throw new InvalidFieldException('Unable to write: Column is protected');
        }

        return $fields;

    }

    /**
     * Filter fields to handle protected prefix.
     * Add to ResourceModel's onDeleting method.
     *
     * @param OrmResource $resource
     * @return void
     * @throws InvalidFieldException
     */
    protected function filterProtectedPrefixDeleting(OrmResource $resource): void
    {

        if ($this->only_protected === true) {

            if (!str_starts_with($resource->get($this->getColumnName(), ''), $this->getProtectedPrefix())) {
                throw new InvalidFieldException('Unable to delete: Column must be protected');
            }

        } else if ($this->with_protected === false) {

            if (str_starts_with($resource->get($this->getColumnName(), ''), $this->getProtectedPrefix())) {
                throw new InvalidFieldException('Unable to delete: Column is protected');
            }

        }

    }

    /**
     * Reset protected prefix filter.
     * Add to ResourceModel's onComplete method.
     *
     * @return void
     */
    private function resetProtectedPrefixFilter(): void
    {
        $this->with_protected = false;
        $this->only_protected = false;
    }

    /*
     * |--------------------------------------------------------------------------
     * | TOTP
     * |--------------------------------------------------------------------------
     */

    /**
     * Get valid, non-expired TOTP from JSON-encoded meta value or throw exception.
     *
     * @param mixed $meta_value
     * @return Totp
     * @throws OrmServiceException
     */
    private function getTotpFromMetaValue(mixed $meta_value): Totp
    {

        // Validate format

        $json = new IsJson($meta_value);

        if (!$json->isValid()) {
            throw new OrmServiceException('Unable to get TOTP: Invalid format');
        }

        $meta_value = json_decode($meta_value, true);

        if (!isset($meta_value['created_at']) || !isset($meta_value['expires_at']) || !isset($meta_value['value'])) {
            throw new OrmServiceException('Unable to get TOTP: Invalid or missing key(s)');
        }

        $totp = new Totp($meta_value);

        // Validate expired

        if ($totp->getExpiresAt() !== 0 && $totp->getExpiresAt() < time()) {
            throw new OrmServiceException('Unable to get TOTP: TOTP is expired');
        }

        return $totp;

    }

    /**
     * Create TOTP (time-based one-time password) and save with hashed value, ensuring wait time has elapsed.
     * Value is hashed using RbacService->createHash().
     * Returns TOTP with raw value.
     *
     * @param string $user_id
     * @param string $meta_key
     * @param int $wait
     * @param int $duration
     * @param int $length
     * @param string $type (Any valid RbacService TOTP_TYPE_* constant)
     * @return Totp
     * @throws AlreadyExistsException (Thrown when wait time has not elapsed)
     * @throws DoesNotExistException (Thrown when user does not exist)
     * @throws UnexpectedException
     */
    public function createTotp(string $user_id, string $meta_key, int $wait, int $duration, int $length, string $type): Totp
    {

        $now = time();

        try {

            $existing = $this->getTotp($user_id, $meta_key);

            if ($existing->getCreatedAt() > $now - ($wait * 60)) {
                throw new AlreadyExistsException('Unable to create TOTP: Wait time not yet elapsed');
            }

        } catch (DoesNotExistException) {
            // Do nothing;
        }

        if ($duration === 0) {
            $expires_at = 0;
        } else {
            $expires_at = $now + ($duration * 60);
        }

        $totp = new Totp([
            'created_at' => $now,
            'expires_at' => $expires_at,
            'value' => Str::random($length, $type)
        ]);

        try {

            $this->withProtectedPrefix()->upsert([
                'user' => $user_id,
                'meta_key' => $meta_key,
                'meta_value' => json_encode([
                    'created_at' => $totp->getCreatedAt(),
                    'expires_at' => $totp->getExpiresAt(),
                    'value' => $this->rbacService->createHash($totp->getValue())
                ])
            ]);

        } catch (InvalidFieldException) {
            throw new UnexpectedException('Unable to create TOTP: Error upserting value');
        }

        return $totp;

    }

    /**
     * Get valid, unexpired TOTP.
     * Quietly deletes if invalid or expired.
     * Value can be verified using RbacService->hashMatches().
     *
     * @param string $user_id
     * @param string $meta_key
     * @return Totp
     * @throws DoesNotExistException
     */
    public function getTotp(string $user_id, string $meta_key): Totp
    {

        $meta_value = $this->ormService->db->single("SELECT meta_value FROM $this->table_name WHERE user = :userId AND meta_key = :metaKey", [
            'userId' => $user_id,
            'metaKey' => $meta_key
        ]);

        if (!$meta_value) {
            throw new DoesNotExistException('Unable to get TOTP: TOTP does not exist');
        }

        try {
            return $this->getTotpFromMetaValue($meta_value);
        } catch (OrmServiceException $e) {
            $this->deleteTotp($user_id, $meta_key);
            throw new DoesNotExistException('Unable to get TOTP: TOTP is invalid or expired', 0, $e);
        }

    }

    /**
     * Quietly delete TOTP, if existing.
     *
     * @param string $user_id
     * @param string $meta_key
     * @return bool
     */
    public function deleteTotp(string $user_id, string $meta_key): bool
    {
        return $this->ormService->db->delete($this->getTableName(), [
            'user' => $user_id,
            'meta_key' => $meta_key
        ]);
    }

    /**
     * Quietly delete all expired TOTP's.
     *
     * @param string $meta_key
     * @return void
     */
    public function deleteExpiredTotps(string $meta_key): void
    {

        $table = $this->getTableName();

        $requests = $this->ormService->db->select("SELECT id, meta_value FROM $table WHERE meta_key = :metaKey", [
            'metaKey' => $meta_key
        ]);

        $delete_ids = [];

        foreach ($requests as $request) {

            try {
                $this->getTotpFromMetaValue($request['meta_value']);
            } catch (OrmServiceException) {
                $delete_ids[] = "'" . $request['id'] . "'";
            }

        }

        if (!empty($delete_ids)) {
            $this->ormService->db->query("DELETE FROM $table WHERE id IN (" . implode(',', $delete_ids) . ")");
        }

    }

}