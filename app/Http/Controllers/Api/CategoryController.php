<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::orderBy('name')->get();

        return $this->sendData([
            'categories' => CategoryResource::collection($categories)->resolve(),
        ], __('messages.categories_retrieved'));
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->first();

        if (! $category) {
            return $this->sendError(__('messages.category_not_found'), 404);
        }

        return $this->sendData([
            'category' => CategoryResource::make($category)->resolve(),
        ], __('messages.category_retrieved'));
    }
}
