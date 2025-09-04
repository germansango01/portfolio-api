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
    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="Retrieve all posts",
     *     tags={"Blog"},
     *     @OA\Response(
     *         response=200,
     *         description="Posts retrieved successfully."
     *     )
     * )
     */
    public function index(PostRequest $request): JsonResponse
    {
        $query = Post::query()->withRelations()->latest();

        return $this->paginateAndRespond($query, $request->validated());
    }

    /**
     * @OA\Get(
     *     path="/api/posts/summary",
     *     summary="Retrieve summary post data",
     *     tags={"Blog"},
     *     @OA\Response(
     *         response=200,
     *         description="Summary retrieved successfully."
     *     )
     * )
     */
    public function summary(): JsonResponse
    {
        $latestPosts = Post::query()->withRelations()->latest()->limit(5)->get();
        $mostViewedPosts = Post::query()->withRelations()->orderByDesc('views')->limit(5)->get();

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
     * @OA\Get(
     *     path="/api/posts/search",
     *     summary="Search for posts",
     *     tags={"Blog"},
     *     @OA\Response(
     *         response=200,
     *         description="Posts retrieved successfully."
     *     )
     * )
     */
    public function search(SearchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $query = Post::query()->withRelations()
            ->searchTerm($validated['q'] ?? null)
            ->filterByCategory($validated['category'] ?? null)
            ->filterByAuthor($validated['author'] ?? null)
            ->filterByTag($validated['tag'] ?? null)
            ->latest();

        return $this->paginateAndRespond($query, $validated);
    }

    /**
     * @OA\Get(
     *     path="/api/posts/category/{categorySlug}",
     *     summary="Retrieve posts by category",
     *     tags={"Blog"},
     *     @OA\Response(
     *         response=200,
     *         description="Posts retrieved successfully."
     *     )
     * )
     */
    public function postsByCategory(PostRequest $request, string $categorySlug): JsonResponse
    {
        $category = Category::where('slug', $categorySlug)->first();

        if (!$category) {
            return $this->sendError(__('messages.category_not_found'), 404);
        }

        $query = Post::query()->withRelations()->where('category_id', $category->id)->latest();

        return $this->paginateAndRespond($query, $request->validated());
    }

    /**
     * @OA\Get(
     *     path="/api/posts/tag/{tagSlug}",
     *     summary="Retrieve posts by tag",
     *     tags={"Blog"},
     *     @OA\Response(
     *         response=200,
     *         description="Posts retrieved successfully."
     *     )
     * )
     */
    public function postsByTag(PostRequest $request, string $tagSlug): JsonResponse
    {
        $tag = Tag::where('slug', $tagSlug)->first();

        if (!$tag) {
            return $this->sendError(__('messages.tag_not_found'), 404);
        }

        $query = Post::query()->withRelations()
            ->whereHas('tags', function (Builder $query) use ($tag) {
                $query->where('tags.id', $tag->id);
            })
            ->latest();

        return $this->paginateAndRespond($query, $request->validated());
    }

    /**
     * @OA\Get(
     *     path="/api/posts/user/{userId}",
     *     summary="Retrieve posts by user",
     *     tags={"Blog"},
     *     @OA\Response(
     *         response=200,
     *         description="Posts retrieved successfully."
     *     )
     * )
     */
    public function postsByUser(PostRequest $request, int $userId): JsonResponse
    {
        $user = User::find($userId);

        if (!$user) {
            return $this->sendError(__('messages.user_not_found'), 404);
        }

        $query = Post::query()->withRelations()->where('user_id', $user->id)->latest();

        return $this->paginateAndRespond($query, $request->validated());
    }

    /**
     * @OA\Get(
     *     path="/api/posts/{slug}",
     *     summary="Retrieve a single post",
     *     tags={"Blog"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Post slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post retrieved successfully."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found."
     *     )
     * )
     */
    public function show(string $slug): JsonResponse
    {
        $post = Post::query()->withRelations()->where('slug', $slug)->first();

        if (!$post) {
            return $this->sendError(__('messages.post_not_found'), 404);
        }

        return $this->sendData(PostResource::make($post)->resolve(), __('messages.post_retrieved'));
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

        $transformed = PostResource::collection($posts)->resolve();

        return $this->sendData([
            'posts' => $transformed,
            'meta'  => [
                'current_page' => $posts->currentPage(),
                'last_page'    => $posts->lastPage(),
                'per_page'     => $posts->perPage(),
                'total'        => $posts->total(),
            ],
            'links' => [
                'first' => $posts->url(1),
                'last'  => $posts->url($posts->lastPage()),
                'prev'  => $posts->previousPageUrl(),
                'next'  => $posts->nextPageUrl(),
            ],
        ], __('messages.posts_retrieved'));
    }
}
