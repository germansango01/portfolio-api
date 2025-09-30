<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $tags = Tag::orderBy('name')->get();

        return $this->sendData([
            'tags' => TagResource::collection($tags)->resolve(),
        ], __('menu.tags_retrieved'));
    }

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
