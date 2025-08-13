<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Enums\FriendStatus;

class MessagingService
{
    public function send(User $sender, int $receiverId, string $body): Message
    {
        if ($sender->id === $receiverId) {
            abort(422, 'Cannot message yourself.');
        }

        [$x, $y] = Friendship::normalizePair($sender->id, $receiverId);

        $areFriends = Friendship::where('user_id', $x)
            ->where('friend_id', $y)
            ->where('status', FriendStatus::Accepted)
            ->exists();

        abort_unless($areFriends, 403, 'Users are not friends.');

        return Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'body' => $body,
        ]);
    }

    public function conversation(User $auth, int $otherId, int $perPage = 20): LengthAwarePaginator
    {
        [$x, $y] = Friendship::normalizePair($auth->id, $otherId);

        $areFriends = Friendship::where('user_id', $x)
            ->where('friend_id', $y)
            ->where('status', FriendStatus::Accepted)
            ->exists();

        abort_unless($areFriends, 403, 'Users are not friends.');

        return Message::with(['sender:id,name', 'receiver:id,name'])
            ->where(function ($q) use ($auth, $otherId) {
                $q->where('sender_id', $auth->id)->where('receiver_id', $otherId)
                    ->orWhere(function ($q) use ($auth, $otherId) {
                        $q->where('sender_id', $otherId)->where('receiver_id', $auth->id);
                    });
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate($perPage);
    }
}
