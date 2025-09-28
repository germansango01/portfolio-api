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
        ], __('menu.success_list'));
    }
}
