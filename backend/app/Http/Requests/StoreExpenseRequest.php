<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // o cabeçalho Idempotency-Key entra nos dados validados como idempotency_key.
    // O merge sobrescreve qualquer idempotency_key que viesse no corpo: só o cabeçalho vale.
    protected function prepareForValidation(): void
    {
        $this->merge(['idempotency_key' => $this->header('Idempotency-Key')]);
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:120'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'spent_on' => ['required', 'date'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('user_id', $this->user()->id)],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ];
    }
}
