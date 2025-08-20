<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", format="int64", description="Identificador único para el usuario"),
 *     @OA\Property(property="name", type="string", description="Nombre del usuario"),
 *     @OA\Property(property="email", type="string", description="Dirección de correo electrónico del usuario"),
 *     @OA\Property(property="photo", type="string", description="Ruta de la foto de perfil del usuario")
 * )
 */
class UserResource extends JsonResource
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
            'email' => $this->email,
        ];
    }
}
