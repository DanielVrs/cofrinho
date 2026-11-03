<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    // roda antes das regras de validação: 403 se o registro for de outro usuário
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('category'));
    }

    public function rules(): array
    {
        return [
            // ignora a própria categoria na checagem de nome único
            'name' => ['required', 'string', 'max:50', Rule::unique('categories', 'name')->where('user_id', $this->user()->id)->ignore($this->category)],
            'color' => ['required', 'hex_color'],
        ];
    }
}
