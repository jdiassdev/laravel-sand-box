<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'customer_email'   => $this->customer_email,
            'status'           => $this->status,
            'total'            => $this->total,
            'payment_id'       => $this->payment_id,
            'shipping_address' => $this->shipping_address,
            'coupon_code'      => $this->coupon_code,
            'notes'            => $this->notes,
            'created_at'       => $this->created_at,
        ];
    }
}
