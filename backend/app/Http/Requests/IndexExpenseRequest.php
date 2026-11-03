<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // valida a query string: ?month=2026-11&category_id=2 (os dois opcionais)
    public function rules(): array
    {
        return [
            'month' => ['nullable', 'date_format:Y-m'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('user_id', $this->user()->id)],
        ];
    }
}
