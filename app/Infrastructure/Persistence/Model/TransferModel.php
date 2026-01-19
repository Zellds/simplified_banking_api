<?php

namespace App\Infrastructure\Persistence\Model;

use App\Domain\Transfer\Transfer;
use App\Domain\Transfer\TransferStatus;
use App\Infrastructure\Persistence\Model\UserModel;
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
        'status' => TransferStatus::class,
    ];

    public function payer(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'payer_id');
    }

    public function payee(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'payee_id');
    }

    public function toEntity()
    {
        return new Transfer(
            id: $this->id,
            protocol: $this->protocol,
            payerId: $this->payer_id,
            payeeId: $this->payee_id,
            amount: $this->amount,
            status: $this->status,
        );
    }
}
