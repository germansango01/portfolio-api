<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Tag",
 *     title="Tag",
 *     description="Tag resource",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name",
 *         example="PHP"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="Slug",
 *         example="php"
 *     )
 * )
 */
class TagResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'posts_count' => $this->when(isset($this->posts_count), $this->posts_count),
        ];
    }
}
