<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
            'id'             => $this->id,
            'title'          => $this->title,
            'slug'           => $this->slug,
            'excerpt'        => Str::limit(strip_tags($this->content), 100),
            'image_url'      => $this->image_url ?? null,
            'created_at'     => $this->created_at->format('Y-m-d H:i'),
            'views'          => $this->views,
            'category'       => $this->category ? [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'user' => $this->user ? [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ] : null,
        ];
    }
}
