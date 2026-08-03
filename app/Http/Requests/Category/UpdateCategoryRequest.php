<?php

declare(strict_types=1);

namespace App\Http\Requests\Category; // Ajuste o namespace se necessário

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
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('categories', 'name')
                    ->ignore($this->route('category'))
                    ->where(function ($query) {
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
            'name.min'      => 'O nome deve ter no mínimo 3 caracteres.',
            'name.unique'   => 'Você já possui uma categoria com este nome para este tipo.',
            'type.required' => 'O campo tipo é obrigatório.',
            'type.in'       => 'O tipo de conta selecionado é inválido.',
        ];
    }
}
