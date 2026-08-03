<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('categories', 'name')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id)
                        ->where('type', $this->input('type'));
                }),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['income', 'expense']),
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'name.unique' => 'Você já possui uma categoria com este nome para este tipo.',

            'type.required' => 'O campo tipo é obrigatório.',
            'type.string' => 'O tipo deve ser um texto válido.',
            'type.in' => 'O tipo de conta selecionado é inválido. Categorias possíveis: income, expense',
        ];
    }
}
