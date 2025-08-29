<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class PostController extends BaseController
{
    /**
     * Retrieve blog data including latest posts, most viewed posts, and posts by category.
     */
    public function resume(): JsonResponse
    {
        $latestPosts = Post::withRelations()->latest()->limit(5)->get();
        $mostViewedPosts = Post::withRelations()->orderByDesc('views')->limit(5)->get();

        $categories = Category::select('id', 'name')
            ->with(['posts' => function ($query) {
                $query->withRelations()->latest()->limit(5);
            }])
            ->get();

        $postsByCategory = $categories->map(fn ($category) => [
            'id'    => $category->id,
            'name'  => $category->name,
            'posts' => PostResource::collection($category->posts)->resolve(),
        ]);

        return $this->sendData([
            'latest_posts'      => PostResource::collection($latestPosts)->resolve(),
            'most_viewed_posts' => PostResource::collection($mostViewedPosts)->resolve(),
            'posts_by_category' => $postsByCategory,
        ], __('messages.blog_retrieved'));
    }

    /**
     * Retrieve paginated list of posts.
     */
    public function posts(PostRequest $request): JsonResponse
    {
        $query = Post::withRelations()->latest();

        return $this->paginateAndRespond($query, $request->validated());
    }

    /**
     * Search for posts with optional filters.
     */
    public function search(PostRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $query = Post::withRelations()
            ->searchTerm($validated['q'] ?? null)
            ->filterByCategory($validated['category'] ?? null)
            ->filterByAuthor($validated['author'] ?? null)
            ->filterByTag($validated['tag'] ?? null)
            ->latest();

        return $this->paginateAndRespond($query, $validated);
    }

    /**
     * Paginate the query and format the response.
     */
    private function paginateAndRespond(Builder $query, array $validated): JsonResponse
    {
        $posts = $query->paginate(
            perPage: $validated['per_page'] ?? 15,
            page: $validated['page'] ?? 1
        );

        $resource = PostResource::collection($posts)->response()->getData(true);

        return $this->sendData([
            'posts' => $resource['data'],
            'meta'  => $resource['meta'],
            'links' => $resource['links'],
        ], __('messages.posts_retrieved'));
    }
}
