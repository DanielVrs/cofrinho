<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'amount_cents' => $this->amount_cents,
            'spent_on' => $this->spent_on->toDateString(),
            // só entra no JSON se a categoria foi carregada com with('category')
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
