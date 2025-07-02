<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'payment_code' => $this->payment_code,
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'external_id' => $this->external_id,
            'payment_details' => $this->payment_details,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'booking' => new BookingResource($this->whenLoaded('booking')),
        ];
    }
}
