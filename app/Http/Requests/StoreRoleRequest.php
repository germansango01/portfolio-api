<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * @OA\Schema(
 *     schema="StoreRoleRequest",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", maxLength=255, description="Name of the role")
 * )
 */
class StoreRoleRequest extends FormRequest
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
            'name' => 'required|string|max:50',
        ];
    }

    /**
     * Failed Validation function.
     *
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        $response = (new BaseController())->sendValidationError($errors);

        throw new HttpResponseException($response);
    }
}
