<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Transfer\TransferService;
use App\Infrastructure\Http\Requests\TransferRequest;
use Illuminate\Http\JsonResponse;

class TransferController
{
    public function __construct(
        private readonly TransferService $transferService
    ) {}

    public function store(TransferRequest $request): JsonResponse
    {
        $protocol = $this->transferService->execute($request->payer, $request->payee, $request->value);

        return response()->json(['code' => 201, 'message' => 'Transfer created', 'protocol' => $protocol], 201);
    }
}
