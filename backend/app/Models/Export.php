<?php

namespace App\Models;

use App\Enums\ExportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Export extends Model
{
    protected $fillable = ['user_id', 'month', 'status', 'path', 'error'];

    // toda exportação nasce pending, mesmo antes de ir ao banco
    protected $attributes = ['status' => 'pending'];

    protected function casts(): array
    {
        return [
            'status' => ExportStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isDone(): bool
    {
        return $this->status === ExportStatus::Done && $this->path !== null;
    }
}
