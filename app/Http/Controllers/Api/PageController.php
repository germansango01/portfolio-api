<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PostsRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

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
    public function posts(PostsRequest $request): JsonResponse
    {
        $perPage = $request->validated('per_page', 15);
        $page = $request->validated('page', 1);

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
    public function search(SearchRequest $request): JsonResponse
    {
        // Obtiene los datos ya validados desde el FormRequest.
        $term = $request->validated('q');
        $categoryId = $request->validated('category');
        $tagId = $request->validated('tag');
        $authorId = $request->validated('author');
        $perPage = $request->validated('per_page', 15);
        $page = $request->validated('page', 1);

        $posts = $this->basePostQuery()
            // Aplica los filtros condicionalmente solo si el valor no es nulo.
            ->when($term, function ($query) use ($term) {
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('title', 'LIKE', "%{$term}%")
                        ->orWhere('content', 'LIKE', "%{$term}%");
                });
            })
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($authorId, fn ($query) => $query->where('user_id', $authorId))
            ->when($tagId, fn ($query) => $query->whereHas('tags', fn ($tagQuery) => $tagQuery->where('tags.id', $tagId)))
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
