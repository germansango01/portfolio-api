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
     * Search for posts with optional filters.
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->input('q');
        $categoryId = $request->integer('category');
        $tagId = $request->integer('tag');
        $authorId = $request->integer('author');
        $perPage = $request->integer('per_page', 15);
        $page = $request->integer('page', 1);

        // Validación rápida: si no hay término ni filtros, error
        if (empty($term) && empty($categoryId) && empty($tagId) && empty($authorId)) {
            return $this->sendError(__('validation.required', ['attribute' => 'q']), 422);
        }

        $posts = $this->basePostQuery()
            ->when($term, function ($q) use ($term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'LIKE', "%{$term}%")
                        ->orWhere('excerpt', 'LIKE', "%{$term}%")
                        ->orWhere('content', 'LIKE', "%{$term}%");
                });
            })
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($authorId, fn ($q) => $q->where('user_id', $authorId))
            ->when($tagId, fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('tags.id', $tagId)))
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
        return Post::select('id', 'title', 'slug', 'content', 'image_url', 'views', 'category_id', 'user_id', 'created_at')
        ->withCount('comments')
        ->with([
            'category:id,name,slug',
            'tags:id,name,slug',
            'user:id,name',
        ]);
    }
}
