<?php

namespace App\Domain\User;

use App\Domain\Wallet\WalletModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserModel extends Model
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'document',
        'type',
    ];

    protected $casts = [
        'type' => UserType::class,
    ];

    public function wallet(): HasOne
    {
        return $this->hasOne(WalletModel::class, 'user_id');
    }

    public function isMerchant(): bool
    {
        return $this->type === UserType::MERCHANT;
    }

    public function isCommon(): bool
    {
        return $this->type === UserType::COMMON;
    }
}
