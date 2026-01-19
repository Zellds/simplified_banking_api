<?php

namespace App\Domain\Transfer\Contracts;

interface AuthorizationInterface
{
    public function authorize(): bool;
}
