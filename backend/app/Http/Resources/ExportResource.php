<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'month' => $this->month,
            'status' => $this->status,
            'error' => $this->error,
            // o link só existe quando o arquivo está pronto
            'download_url' => $this->isDone() ? route('exports.download', $this->resource) : null,
            'created_at' => $this->created_at,
        ];
    }
}
