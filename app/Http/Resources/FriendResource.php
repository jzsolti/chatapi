<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FriendResource extends JsonResource
{
    public function toArray($request): array
    {
        // $this = Friendship model (with user, friend loaded)
        $authId = optional($request->user())->id;
        $other  = $this->user_id === $authId ? $this->friend : $this->user;

        return [
            'id'    => $other->id,
            'name'  => $other->name,
            'email' => $other->email,
            'status' => $this->status?->value,
            'since' => $this->created_at?->toISOString(),
        ];
    }
}
