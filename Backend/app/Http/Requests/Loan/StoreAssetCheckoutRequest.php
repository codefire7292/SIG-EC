<?php

declare(strict_types=1);

namespace App\Http\Requests\Loan;

use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'asset_id' => [
                'required',
                'integer',
                Rule::exists('assets', 'id'),
                // Asset must be available
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $asset = Asset::find($value);
                    if ($asset && !$asset->isAvailable()) {
                        $fail('Cet équipement n\'est pas disponible (statut actuel : ' . $asset->status . ').');
                    }
                },
            ],
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'signature' => [
                'required',
                'string',
                'regex:/^data:image\/(png|jpeg);base64,/',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'signature.required' => 'La signature numérique est obligatoire.',
            'signature.regex'    => 'Le format de la signature est invalide.',
        ];
    }
}
