<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Services\TransferService;
use App\Infrastructure\Http\Requests\TransferRequest;
use Illuminate\Http\JsonResponse;

class TransferController
{
    public function __construct(
        private readonly TransferService $transferService
    ) {}

    public function store(TransferRequest $request): JsonResponse
    {
        $protocol = $this->transferService->execute($request->payer, $request->payee, $request->amount_cents);

        return response()->json(['code' => 201, 'message' => 'Transfer created', 'protocol' => $protocol], 201);
    }
}
