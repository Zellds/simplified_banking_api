<?php

namespace App\Infrastructure\External\Authorization;

use App\Domain\Transfer\Contracts\AuthorizationInterface;
use Illuminate\Support\Facades\Http;

class HttpAuthorizationClient implements AuthorizationInterface
{
    public function authorize(): bool
    {
        $response = Http::get(env('AUTHORIZATION_SERVICE_URL'));

        if (!$response->successful()) {
            return false;
        }

        return (bool) data_get($response->json(), 'data.authorization', false);
    }
}
