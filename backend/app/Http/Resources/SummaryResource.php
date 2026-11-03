<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'month' => $this['month'],
            'total_cents' => $this['total_cents'],
            'by_category' => $this['by_category'],
        ];
    }
}
