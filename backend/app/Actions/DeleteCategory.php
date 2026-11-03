<?php

namespace App\Actions;

use App\Exceptions\CategoryInUseException;
use App\Models\Category;

class DeleteCategory
{
    public function handle(Category $category): void
    {
        if ($category->expenses()->exists()) {
            throw new CategoryInUseException();
        }

        $category->delete();
    }
}
