<?php

namespace Bayfront\BonesService\Rbac\Traits;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\SimplePdo\Exceptions\QueryException;
use Bayfront\SimplePdo\Query;

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

}