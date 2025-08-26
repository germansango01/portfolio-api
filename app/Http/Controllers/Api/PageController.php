<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            __('messages.blog_retrieved')
        );
    }

    /**
     * Get paginated posts.
     */
    public function posts(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $posts = Post::withCount('comments')
            ->with('category:id,name', 'user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Obtenemos los datos de la respuesta del recurso para construir nuestra respuesta paginada personalizada
        $paginatedResponse = PostResource::collection($posts)->response()->getData(true);

        return $this->sendData(
            [
                'posts' => $paginatedResponse['data'],
                'meta' => $paginatedResponse['meta'],
                'links' => $paginatedResponse['links'],
            ],
            __('messages.posts_retrieved')
        );
    }
}
