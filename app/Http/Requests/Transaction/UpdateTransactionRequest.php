<?php

declare(strict_types=1);

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                }),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id)
                        ->where('type', $this->input('type'));
                }),
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['income', 'expense']),
            ],
            'date' => [
                'required',
                'date',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'is_paid' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.required'  => 'A conta é obrigatória.',
            'account_id.exists'    => 'A conta selecionada é inválida ou não pertence a você.',
            'category_id.required' => 'A categoria é obrigatória.',
            'category_id.exists'   => 'A categoria selecionada é inválida ou não corresponde ao tipo da transação.',
            'amount.required'      => 'O valor é obrigatório.',
            'amount.numeric'       => 'O valor deve ser um número válido.',
            'amount.min'           => 'O valor não pode ser negativo.',
            'type.required'        => 'O tipo da transação é obrigatório.',
            'type.in'              => 'O tipo deve ser "income" ou "expense".',
            'date.required'        => 'A data é obrigatória.',
            'date.date'            => 'Informe uma data válida.',
            'is_paid.required'     => 'O status de pagamento é obrigatório.',
            'is_paid.boolean'      => 'O status de pagamento deve ser verdadeiro ou falso.',
        ];
    }
}
