<?php

namespace App\Http\Controllers;

use App\Actions\DeleteCategory;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        // só as categorias do usuário autenticado
        return CategoryResource::collection($request->user()->categories()->orderBy('name')->get());
    }

    public function store(StoreCategoryRequest $request)
    {
        // criar pela relação já preenche o user_id
        $category = $request->user()->categories()->create($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Category $category)
    {
        Gate::authorize('view', $category); // 403 se for de outro usuário

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        // a autorização de update está no UpdateCategoryRequest::authorize()
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    public function destroy(Category $category, DeleteCategory $deleteCategory)
    {
        Gate::authorize('delete', $category);

        $deleteCategory->handle($category);

        return response()->noContent();
    }
}
