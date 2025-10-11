<?php

namespace App\Http\Requests;

/**
 * @OA\Schema(
 *     schema="PostRequest",
 *     title="Post Request",
 *     description="Post request body",
 *     @OA\Property(
 *         property="page",
 *         type="integer",
 *         description="Page number",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="per_page",
 *         type="integer",
 *         description="Items per page",
 *         example=10
 *     )
 * )
 */
class PostRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
