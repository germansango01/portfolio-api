<?php

namespace App\Http\Controllers\Api;

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
            'posts' => PostResource::collection($category->posts),
        ]);

        return $this->sendData([
            'latest_posts'      => PostResource::collection($latestPosts),
            'most_viewed_posts' => PostResource::collection($mostViewedPosts),
            'posts_by_category' => $postsByCategory,
        ], __('messages.blog_retrieved'));
    }

    /**
     * Retrieve paginated list of posts.
     */
    public function posts(PostsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $posts = Post::withRelations()
            ->latest()
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1
            );

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
        $validated = $request->validated();

        $posts = Post::withRelations()
            ->searchTerm($validated['q'] ?? null)
            ->filterByCategory($validated['category'] ?? null)
            ->filterByAuthor($validated['author'] ?? null)
            ->filterByTag($validated['tag'] ?? null)
            ->latest()
            ->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1
            );

        $paginated = PostResource::collection($posts)->response()->getData(true);

        return $this->sendData([
            'posts' => $paginated['data'],
            'meta'  => $paginated['meta'],
            'links' => $paginated['links'],
        ], __('messages.posts_retrieved'));
    }
}
