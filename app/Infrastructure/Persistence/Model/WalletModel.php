<?php

namespace App\Infrastructure\Persistence\Model;

use App\Domain\Wallet\Wallet;
use App\Infrastructure\Persistence\Model\UserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletModel extends Model
{
    use HasFactory;

    protected $table = 'wallets';

    protected $fillable = [
        'user_id',
        'balance',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function toEntity()
    {
        return new Wallet(
            id: $this->id,
            userId: $this->user_id,
            balance:  $this->balance,
        );
    }
}
