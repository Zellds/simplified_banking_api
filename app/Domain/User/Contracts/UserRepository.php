<?php

namespace App\Domain\User\Contracts;

use App\Domain\User\User;

interface UserRepository
{
    public function findById(int $userId): ?User;
}
