<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // La autorización ya se maneja en el middleware 'auth:api' de la ruta.
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
            'q'        => 'required_without_all:category,tag,author|nullable|string|min:3',
            'category' => 'nullable|integer|exists:categories,id',
            'tag'      => 'nullable|integer|exists:tags,id',
            'author'   => 'nullable|integer|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page'     => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'q.required_without_all' => 'Debe proporcionar un término de búsqueda o seleccionar un filtro.',
            'q.min'                  => 'El término de búsqueda debe tener al menos :min caracteres.',
            'category.exists'        => 'La categoría seleccionada no es válida.',
            'tag.exists'             => 'La etiqueta seleccionada no es válida.',
            'author.exists'          => 'El autor seleccionado no es válido.',
        ];
    }
}
