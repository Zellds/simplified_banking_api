<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\User\User;
use App\Domain\User\Contracts\UserRepository;
use App\Infrastructure\Persistence\Model\UserModel;

class UserRepositoryEloquent implements UserRepository
{
	public function findById(int $id): ?User
	{
		$model = UserModel::query()->find($id);

		if (!$model) {
			return null;
		}

		return $model->toEntity();
	}
}
