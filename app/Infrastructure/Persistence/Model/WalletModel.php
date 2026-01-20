<?php

namespace App\Infrastructure\Persistence\Model;

use App\Domain\Wallet\Wallet;
use Illuminate\Database\Eloquent\Model;

class WalletModel extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'user_id',
        'balance', 
    ];

    protected $casts = [
        'balance' => 'integer',
        'user_id' => 'integer',
    ];

    public function toEntity(): Wallet
    {
        return new Wallet(
            id: $this->id,
            userId: $this->user_id,
            balance: $this->balance
        );
    }
}
