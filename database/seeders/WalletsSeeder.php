<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Model\WalletModel;
use Illuminate\Database\Seeder;

class WalletsSeeder extends Seeder
{
    public function run(): void
    {
        WalletModel::insert([
            [
                'user_id' => 1,
                'balance' => 10000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'balance' => 500000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'balance' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
