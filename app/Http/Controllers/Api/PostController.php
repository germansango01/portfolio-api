<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PostRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class PostController extends BaseController
{
    public function index(PostRequest $request): JsonResponse
    {
        $query = Post::with(['user', 'category', 'tags'])->latest();

        return $this->paginateAndRespond($query, $request->validated());
    }

    public function summary(): JsonResponse
    {
        $latestPosts = Post::with(['user', 'category', 'tags'])->latest()->limit(5)->get();
        $mostViewedPosts = Post::with(['user', 'category', 'tags'])->orderByDesc('views')->limit(5)->get();

        $categories = Category::with(['posts' => fn($q) => $q->with(['user', 'tags'])->latest()->limit(5)])->get();

        $postsByCategory = $categories->map(fn($category) => [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'posts' => PostResource::collection($category->posts)->resolve(),
        ]);

        return $this->sendData([
            'latest_posts' => PostResource::collection($latestPosts)->resolve(),
            'most_viewed_posts' => PostResource::collection($mostViewedPosts)->resolve(),
            'posts_by_category' => $postsByCategory,
        ], __('messages.blog_retrieved'));
    }

    public function search(SearchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $query = Post::with(['user', 'category', 'tags'])
            ->searchTerm($validated['q'] ?? null)
            ->filterByCategory($validated['category'] ?? null)
            ->filterByAuthor($validated['author'] ?? null)
            ->filterByTag($validated['tag'] ?? null)
            ->latest();

        return $this->paginateAndRespond($query, $validated);
    }

    public function postsByCategory(PostRequest $request, Category $category): JsonResponse
    {
        $query = Post::with(['user', 'category', 'tags'])->where('category_id', $category->id)->latest();
        return $this->paginateAndRespond($query, $request->validated());
    }

    public function postsByTag(PostRequest $request, Tag $tag): JsonResponse
    {
        $query = Post::with(['user', 'category', 'tags'])
            ->whereHas('tags', fn(Builder $q) => $q->where('tags.id', $tag->id))
            ->latest();

        return $this->paginateAndRespond($query, $request->validated());
    }

    public function postsByUser(PostRequest $request, User $user): JsonResponse
    {
        $query = Post::with(['user', 'category', 'tags'])->where('user_id', $user->id)->latest();
        return $this->paginateAndRespond($query, $request->validated());
    }

    public function show(Post $post): JsonResponse
    {
        $post->load(['user', 'category', 'tags']);

        return $this->sendData([
            'post' => PostResource::make($post)->resolve(),
        ], __('messages.post_retrieved'));
    }

    private function paginateAndRespond(Builder $query, array $validated): JsonResponse
    {
        $posts = $query->paginate(
            perPage: $validated['per_page'] ?? 15,
            page: $validated['page'] ?? 1,
        );

        $transformed = PostResource::collection($posts)->resolve();

        return $this->sendData([
            'posts' => $transformed,
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
            'links' => [
                'first' => $posts->url(1),
                'last' => $posts->url($posts->lastPage()),
                'prev' => $posts->previousPageUrl(),
                'next' => $posts->nextPageUrl(),
            ],
        ], __('messages.posts_retrieved'));
    }
}
