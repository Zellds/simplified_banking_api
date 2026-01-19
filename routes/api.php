<?php

use App\Infrastructure\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::post('/transfer', [TransferController::class, 'store']);