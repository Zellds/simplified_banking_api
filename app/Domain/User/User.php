<?php

namespace App\Domain\User;

class User
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $document,
        public UserType $type, 
    ) {}

    public function isMerchant(): bool
    {
        return $this->type === UserType::MERCHANT;
    }

    public function isCommon(): bool
    {
        return $this->type === UserType::COMMON;
    }
}
