<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// depósito e saque: só o dono do cofre, valor inteiro em centavos
class VaultMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('vault'));
    }

    public function rules(): array
    {
        return [
            'amount_cents' => ['required', 'integer', 'min:1'],
        ];
    }
}
