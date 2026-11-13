<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BookingStoreRequest extends FormRequest
{
    
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'media_id' => ['required', 'integer', 'exists:media,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'starts_at' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'ends_at' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_at'],
        ];
    }
}
