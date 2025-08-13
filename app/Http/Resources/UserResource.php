<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'is_verified' => $this->hasVerifiedEmail(),
            'created_at' => $this->created_at?->toISOString(),
            'is_current_user' => auth()->id() === $this->id,
        ];
    }
}
