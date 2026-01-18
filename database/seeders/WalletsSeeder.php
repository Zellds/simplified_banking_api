<?php

namespace Database\Seeders;

use App\Domain\Wallet\WalletModel;
use Illuminate\Database\Seeder;

class WalletsSeeder extends Seeder
{
    public function run(): void
    {
        WalletModel::insert([
            [
                'user_id' => 1,
                'balance' => 1000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'balance' => 500.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'balance' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
