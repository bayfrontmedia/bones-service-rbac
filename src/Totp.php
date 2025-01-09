<?php

namespace Bayfront\BonesService\Rbac;

use Bayfront\ArrayHelpers\Arr;

class Totp
{

    private array $totp;

    public function __construct(array $totp)
    {
        $this->totp = $totp;
    }

    /**
     * Get entire TOTP array.
     *
     * @return array
     */
    public function getTotp(): array
    {
        return $this->totp;
    }

    /**
     * Get created_at timestamp.
     *
     * @return int
     */
    public function getCreatedAt(): int
    {
        return Arr::get($this->totp, 'created_at', 0);
    }

    /**
     * Get expires_at timestamp.
     *
     * @return int
     */
    public function getExpiresAt(): int
    {
        return Arr::get($this->totp, 'expires_at', 0);
    }

    /**
     * Get TOTP value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return Arr::get($this->totp, 'value', '');
    }

}