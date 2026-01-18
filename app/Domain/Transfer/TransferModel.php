<?php

namespace App\Domain\Transfer;

use App\Domain\User\UserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferModel extends Model
{
    use HasFactory;

    protected $table = 'transfers';

    protected $fillable = [
        'protocol',
        'payer_id',
        'payee_id',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payer(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'payer_id');
    }

    public function payee(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'payee_id');
    }
}
