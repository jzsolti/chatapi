<?php

namespace App\Services;

use App\Enums\FriendStatus;
use App\Models\Friendship;
use App\Models\User;

class FriendshipService
{
    public function send(User $auth, int $targetId)
    {
        if ($auth->id === $targetId) {
            abort(422, 'You cannot friend yourself.');
        }

        $target = User::active()->findOrFail($targetId);

        [$x, $y] = Friendship::normalizePair($auth->id, $target->id);

        return Friendship::firstOrCreate(
            ['user_id' => $x, 'friend_id' => $y],
            ['status' => FriendStatus::Pending]
        );
    }

    public function accept(User $auth, int $otherId)
    {
        if ($auth->id === $otherId) {
            abort(422, 'Invalid operation.');
        }

        [$x, $y] = Friendship::normalizePair($auth->id, $otherId);

        $friendship = Friendship::where('user_id', $x)->where('friend_id', $y)->firstOrFail();

        if ($friendship->status !== FriendStatus::Accepted) {
            $friendship->update(['status' => FriendStatus::Accepted]);
        }

        return $friendship->refresh();
    }

    public function areFriends(int $a, int $b): bool
    {
        [$x, $y] = Friendship::normalizePair($a, $b);

        return Friendship::where('user_id', $x)
            ->where('friend_id', $y)
            ->where('status', FriendStatus::Accepted)
            ->exists();
    }

    public function listFriends(User $auth)
    {
        return Friendship::where('status', FriendStatus::Accepted)
            ->where(fn($q) => $q->where('user_id', $auth->id)
                ->orWhere('friend_id', $auth->id))
            ->with(['user:id,name,email', 'friend:id,name,email'])
            ->get();
    }
}
