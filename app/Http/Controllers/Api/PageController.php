<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PageController extends BaseController
{
    public function blog(): JsonResponse
    {
        // Últimos 5 posts
        $latestPosts = Post::withCount('comments')
            ->with('category:id,name', 'user:id,name')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Top 5 más leídos
        $mostViewedPosts = Post::withCount('comments')
            ->with('category:id,name', 'user:id,name')
            ->orderBy('views', 'desc')
            ->take(5)
            ->get();

        // 5 posts por categoría
        $categories = Category::with(['posts' => function ($query) {
            $query->withCount('comments')
                ->with('category:id,name', 'user:id,name')
                ->orderBy('created_at', 'desc')
                ->take(5);
        }])->get();

        $postsByCategory = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'posts' => PostResource::collection($category->posts),
            ];
        });

        return $this->sendData(
            [
                'latest_posts' => PostResource::collection($latestPosts),
                'most_viewed_posts' => PostResource::collection($mostViewedPosts),
                'posts_by_category' => $postsByCategory,
            ],
            __('auth.success_login')
        );

    }
}
