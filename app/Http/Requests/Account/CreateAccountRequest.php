<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['checking', 'savings', 'investment']),
            ],
            'balance' => [
                'required',
                'numeric',
                'min:0',
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

            'type.required' => 'O campo tipo é obrigatório.',
            'type.string' => 'O tipo deve ser um texto válido.',
            'type.in' => 'O tipo de conta selecionado é inválido.',

            'balance.required' => 'O campo saldo é obrigatório.',
            'balance.numeric' => 'O saldo deve ser um valor numérico válido.',
            'balance.min' => 'O saldo inicial não pode ser negativo.',
        ];
    }
}
