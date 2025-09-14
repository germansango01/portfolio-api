<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="MenuItem",
 *     title="Menu Item",
 *     description="Menu item resource",
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
 *         example="Home"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title",
 *         example="Home"
 *     ),
 *     @OA\Property(
 *         property="url",
 *         type="string",
 *         description="URL",
 *         example="/"
 *     ),
 *     @OA\Property(
 *         property="position",
 *         type="integer",
 *         description="Position",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="menu_id",
 *         type="integer",
 *         description="Menu ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="parent_id",
 *         type="integer",
 *         description="Parent ID",
 *         example=null
 *     ),
 *     @OA\Property(
 *         property="children",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/MenuItem")
 *     )
 * )
 */
class MenuItemResource extends JsonResource
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
            'label' => $this->label,
            'icon' => $this->icon,
            'route' => $this->route,
            'url' => $this->url,
            'shortcut' => $this->shortcut,
            'position' => $this->position,
            'parent_id' => $this->parent_id,
            'items' => $this->whenLoaded('children', function () { return self::collection($this->children); }),
        ];
    }
}
