<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ignora a própria categoria na checagem de nome único
            'name' => ['required', 'string', 'max:50', Rule::unique('categories', 'name')->ignore($this->category)],
            'color' => ['required', 'hex_color'],
        ];
    }
}
