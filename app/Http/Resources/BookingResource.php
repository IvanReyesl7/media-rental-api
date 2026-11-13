<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'media_id' => $this->media_id,
        'customer_id' => $this->customer_id,
        'starts_at' => $this->starts_at->format('Y-m-d'),
        'ends_at' => $this->ends_at->format('Y-m-d'),
        'total_price' => (float) $this->total_price,
        'status' => $this->status,
        'media' => new MediaResource($this->whenLoaded('media')),
        'customer' => new CustomerResource($this->whenLoaded('customer')),
    ];
}
}
