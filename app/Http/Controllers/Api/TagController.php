<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/tags",
     *     summary="Retrieve all tags",
     *     tags={"Tags"},
     *     @OA\Response(
     *         response=200,
     *         description="Tags retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/Tag"))
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $tags = Tag::orderBy('name')->get();

        return $this->sendData([
            'tags' => TagResource::collection($tags)->resolve(),
        ], __('menu.tags_retrieved'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tags/{slug}",
     *     summary="Retrieve a single tag by slug",
     *     tags={"Tags"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Tag slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="tag", ref="#/components/schemas/Tag")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found."
     *     )
     * )
     */
    public function show(string $slug): JsonResponse
    {
        $tag = Tag::where('slug', $slug)->first();

        if (! $tag) {
            return $this->sendError(__('messages.tag_not_found'), 404);
        }

        return $this->sendData([
            'tag' => TagResource::make($tag)->resolve(),
        ], __('messages.tag_retrieved'));
    }
}
