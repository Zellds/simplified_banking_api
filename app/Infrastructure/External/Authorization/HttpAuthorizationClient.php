<?php

namespace App\Infrastructure\External\Authorization;

use App\Application\Transfer\Contracts\Clients\AuthorizationClientInterface;
use Illuminate\Support\Facades\Http;

class HttpAuthorizationClient implements AuthorizationClientInterface
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
