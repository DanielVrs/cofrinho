<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:120'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'spent_on' => ['required', 'date'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }
}
