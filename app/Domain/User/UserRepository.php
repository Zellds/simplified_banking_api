<?php

namespace App\Domain\User;

use App\Domain\User\UserModel;

interface UserRepository
{
    public function findById(int $userId): ?UserModel;
}
