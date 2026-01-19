<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Transfer\Contracts\Repositories\UserRepository;
use App\Domain\User\UserModel;

class UserRepositoryEloquent implements UserRepository
{
	public function findById($userId): ?UserModel
	{
		return UserModel::find($userId);
	}
}
