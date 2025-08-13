<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'sender'      => new UserMiniResource($this->whenLoaded('sender')),
            'receiver'    => new UserMiniResource($this->whenLoaded('receiver')),
            'sender_id'   => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'body'        => $this->body,
            'read_at'     => $this->read_at?->toISOString(),
            'created_at'  => $this->created_at?->toISOString(),
        ];
    }
}
