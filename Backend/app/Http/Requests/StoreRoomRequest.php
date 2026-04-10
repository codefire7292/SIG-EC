<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255', 'unique:rooms,nom'],
            'capacite' => ['required', 'integer', 'min:1'],
            'type_salle' => ['required', 'string', 'max:255'],
        ];
    }
}
