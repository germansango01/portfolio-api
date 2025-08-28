<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuItemRequest extends FormRequest
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
            'name' => 'required|string|unique:menu_items,name',
            'title' => 'required|string',
            'url' => 'required|url',
            'parent_id' => 'nullable|exists:menu_items,id',
            'position' => 'required|integer',
            'menu_id' => 'required|exists:menus,id',
        ];
    }
}
