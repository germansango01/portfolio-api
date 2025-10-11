<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     summary="Retrieve all categories",
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="categories", type="array", @OA\Items(ref="#/components/schemas/Category"))
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::orderBy('name')->get();

        return $this->sendData([
            'categories' => CategoryResource::collection($categories)->resolve(),
        ], __('messages.categories_retrieved'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{slug}",
     *     summary="Retrieve a single category by slug",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Category slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="category", ref="#/components/schemas/Category")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found."
     *     )
     * )
     */
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
