<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function listActive(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return User::active()
            ->search($search)
            ->orderBy('name')
            ->paginate($perPage);
    }
}
