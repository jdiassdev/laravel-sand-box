<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    /**
     * @return array{id: int, name: string, description: string|null, price: float, stock: int, category_id: int, active: bool, meta: string|null, created_at: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => $this->price,
            'stock'       => $this->stock,
            'category_id' => $this->category_id,
            'active'      => $this->active,
            'meta'        => $this->meta,
            'created_at'  => $this->created_at,
        ];
    }
}
