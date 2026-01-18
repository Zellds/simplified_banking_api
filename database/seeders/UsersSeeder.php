<?php

namespace Database\Seeders;

use App\Domain\User\UserModel;
use App\Domain\User\UserType;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        UserModel::insert([
            [
                'id'       => 1,
                'name'     => 'Common User With Balance',
                'email'    => 'payer@example.com',
                'document' => '11111111111',
                'type'     => UserType::COMMON,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'       => 2,
                'name'     => 'Merchant User',
                'email'    => 'merchant@example.com',
                'document' => '22222222222',
                'type'     => UserType::MERCHANT,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'       => 3,
                'name'     => 'Common User Without Balance',
                'email'    => 'nosaldo@example.com',
                'document' => '33333333333',
                'type'     => UserType::COMMON,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
