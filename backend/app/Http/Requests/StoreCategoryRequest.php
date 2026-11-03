<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // único só entre as categorias deste usuário
            'name' => ['required', 'string', 'max:50', Rule::unique('categories', 'name')->where('user_id', $this->user()->id)],
            'color' => ['required', 'hex_color'],
        ];
    }
}
