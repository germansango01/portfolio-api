<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="Post",
 *     title="Post",
 *     description="Post resource",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title",
 *         example="My first post"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="Slug",
 *         example="my-first-post"
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         description="Content",
 *         example="<p>This is my first post.</p>"
 *     ),
 *     @OA\Property(
 *         property="excerpt",
 *         type="string",
 *         description="Excerpt",
 *         example="This is my first post."
 *     ),
 *     @OA\Property(
 *         property="image_url",
 *         type="string",
 *         description="Image URL",
 *         example="http://example.com/image.jpg"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Created at",
 *         example="2025-08-26 12:00:00"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Updated at",
 *         example="2025-08-26 12:00:00"
 *     ),
 *     @OA\Property(
 *         property="views",
 *         type="integer",
 *         description="Views",
 *         example=100
 *     ),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         ref="#/components/schemas/User"
 *     ),
 *     @OA\Property(
 *         property="category",
 *         type="object",
 *         ref="#/components/schemas/Category"
 *     ),
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Tag")
 *     ),
 *     @OA\Property(
 *         property="comments_count",
 *         type="integer",
 *         description="Comments count",
 *         example=10
 *     )
 * )
 */
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => Str::limit(strip_tags($this->content), 100),
            'image_url' => $this->image_url ?? null,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i'),
            'views' => $this->views,
            // relaciones optimizadas
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'tags' => $this->whenLoaded(
                'tags',
                fn() => $this->tags->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ]),
            ),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
        ];
    }
}
