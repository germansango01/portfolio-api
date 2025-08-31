<?php

namespace App\Http\Requests;

/**
 * @OA\Schema(
 *     schema="SearchRequest",
 *     title="Search Request",
 *     description="Search request body",
 *     required={"q"},
 *     @OA\Property(
 *         property="q",
 *         type="string",
 *         description="Search query",
 *         example="laravel"
 *     ),
 *     @OA\Property(
 *         property="category",
 *         type="integer",
 *         description="Category ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="author",
 *         type="integer",
 *         description="Author ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="tag",
 *         type="integer",
 *         description="Tag ID",
 *         example=1
 *     ),
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
class SearchRequest extends BaseFormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q'        => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'author'   => ['nullable', 'integer', 'exists:users,id'],
            'tag'      => ['nullable', 'integer', 'exists:tags,id'],
            'page'     => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
