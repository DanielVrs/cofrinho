<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color'];

    // lado "1" da relação: uma categoria tem vários lançamentos
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
