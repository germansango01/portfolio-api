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
        ], __('menu.success_list'));
    }
}
