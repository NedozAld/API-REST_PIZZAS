<?php

namespace App\Http\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;

class AsignarRolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('sanctum')->check() && auth('sanctum')->user()->rol_id !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'rol_id' => 'required|exists:roles,id|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'rol_id.required' => 'El rol es requerido',
            'rol_id.exists' => 'El rol seleccionado no existe',
        ];
    }
}
