<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Domain\Transfer\Exceptions\UnauthorizedTransferException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->renderable(function (UserNotFoundException $e) {
            return response()->json(['code' => 404, 'message' => $e->getMessage()], 404);
        });

        $exceptions->renderable(function (WalletNotFoundException $e) {
            return response()->json(['code' => 404, 'message' => $e->getMessage()], 404);
        });

        $exceptions->renderable(function (UnauthorizedTransferException $e) {
            return response()->json(['code' => 403, 'message' => $e->getMessage()], 403);
        });

        $exceptions->renderable(function (InsufficientBalanceException $e) {
            return response()->json(['code' => 422, 'message' => $e->getMessage()], 422);
        });
    })->create();
