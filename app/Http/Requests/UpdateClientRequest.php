<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => [
                'required', 'string', 'max:50',
                Rule::unique('clients', 'phone')
                    ->ignore(optional($this->route('client'))->id)
                    ->whereNull('deleted_at'),
            ],
            'email' => [
                'nullable', 'string', 'max:255',
                Rule::unique('clients','email')
                    ->ignore(optional($this->route('client'))->id)
                    ->whereNull('deleted_at'),
            ],
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'email.unique' => "Cet e-mail est déjà utilisé.",
        ];
    }
}
