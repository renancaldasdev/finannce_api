<?php

declare(strict_types=1);

namespace App\Http\Requests\Transaction;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionTransferRequest extends FormRequest
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
            'origin_account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'destination_account_id' => [
                'required',
                'integer',
                'different:origin_account_id',
                Rule::exists('accounts', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'origin_category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)->where('type', 'expense')
                ),
            ],
            'destination_category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)->where('type', 'income')
                ),
            ],
            'amount' => ['required', 'numeric', 'min:1'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
