<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends BaseController
{
    /**
     * Retrieve blog data including latest posts, most viewed posts, and posts by category.
     */
    public function blog(): JsonResponse
    {
        $latestPosts = $this->basePostQuery()
            ->latest()
            ->limit(5)
            ->get();

        $mostViewedPosts = $this->basePostQuery()
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        $categories = Category::select('id', 'name')
            ->with(['posts' => function ($query) {
                $query->latest()
                    ->limit(5)
                    ->withCount('comments')
                    ->with('category:id,name', 'user:id,name');
            }])
            ->get();

        $postsByCategory = $categories->map(fn ($category) => [
            'id' => $category->id,
            'name' => $category->name,
            'posts' => PostResource::collection($category->posts),
        ]);

        return $this->sendData([
            'latest_posts' => PostResource::collection($latestPosts),
            'most_viewed_posts' => PostResource::collection($mostViewedPosts),
            'posts_by_category' => $postsByCategory,
        ], __('messages.blog_retrieved'));
    }

    /**
     * Retrieve paginated list of posts.
     */
    public function posts(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $page = $request->integer('page', 1);

        $posts = $this->basePostQuery()
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        $paginated = PostResource::collection($posts)->response()->getData(true);

        return $this->sendData([
            'posts' => $paginated['data'],
            'meta'  => $paginated['meta'],
            'links' => $paginated['links'],
        ], __('messages.posts_retrieved'));
    }

    /**
     * Base query for posts with common relationships and counts.
     */
    private function basePostQuery()
    {
        return Post::select('id', 'title', 'slug', 'category_id', 'user_id', 'created_at')
        ->withCount('comments')
        ->with([
            'category:id,name',
            'tags:id,name',
            'user:id,name',
        ]);
    }
}
