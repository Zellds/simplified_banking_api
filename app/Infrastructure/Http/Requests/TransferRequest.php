<?php

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount_cents' => ['required', 'integer', 'min:1'],
            'payer' => ['required', 'integer', 'exists:users,id'],
            'payee' => ['required', 'integer', 'different:payer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'payee.different' => 'The payee must be different from the payer.',
        ];
    }
}
