<?php

namespace App\Models;

use App\Enums\FriendStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'friend_id', 'status'];

    protected $casts = [
        'status' => FriendStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    /**
     * Normalize a pair of user IDs into a deterministic ascending order.
     *
     * Ensures that a friendship between two users is represented by a single
     * database row regardless of which user initiated the action. This allows
     * you to enforce a unique constraint on (user_id, friend_id) without
     * storing two mirrored rows.
     *
     * Invariant: the returned array always satisfies [$first <= $second].
     *
     * Examples:
     *  - normalizePair(12, 5)  => [5, 12]
     *  - normalizePair(5, 12)  => [5, 12]
     *  - normalizePair(7, 7)   => [7, 7]
     *
     * @param int $a First user ID.
     * @param int $b Second user ID.
     * @return array{0:int,1:int} Two-element array [smallerId, largerId].
     */
    public static function normalizePair(int $a, int $b): array
    {
        return $a < $b ? [$a, $b] : [$b, $a];
    }
}
