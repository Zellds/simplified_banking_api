<?php

namespace App\Application\Transfer\Contracts\Repositories;

use App\Domain\User\UserModel;

interface UserRepository
{
    public function findById(int $userId): ?UserModel;
}
