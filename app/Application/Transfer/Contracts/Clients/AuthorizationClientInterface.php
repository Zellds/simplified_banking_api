<?php

namespace App\Application\Transfer\Contracts\Clients;

interface AuthorizationClientInterface
{
    public function authorize(): bool;
}
