<?php

namespace App\Infrastructure\Persistence\Model;

use App\Domain\User\User;
use App\Domain\User\UserType;
use App\Infrastructure\Persistence\Model\WalletModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function toEntity()
    {
        return new User(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            document: $this->document,
            type: $this->type
        );
    }
}
