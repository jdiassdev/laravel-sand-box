<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => (int) $this->id,
            'name'       => (string) $this->name,
            'email'      => (string) $this->email,
            'avatar_url' => (string) $this->avatar_url,
            'bio'        => (string) $this->bio,
            'created_at' => (string) $this->created_at,
        ];
    }
}
