<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'type' => $this->type,
            'date' => $this->date,
            'description' => $this->description,
            'is_paid' => (bool) $this->is_paid,

            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
        ];
    }
}
