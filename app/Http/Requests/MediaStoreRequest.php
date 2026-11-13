<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MediaStoreRequest extends FormRequest
{

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'location' => ['required', 'string', 'max:255'],
            'dimensions' => ['required', 'string', 'max:50'],
            'price_per_day' => ['required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }
}
