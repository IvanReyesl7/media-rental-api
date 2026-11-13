<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MediaUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'max:50'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'dimensions' => ['sometimes', 'required', 'string', 'max:50'],
            'price_per_day' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', 'in:active,inactive'],
        ];
    }
}
