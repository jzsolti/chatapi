<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class UserController extends Controller
{

    public function __construct(private UserService $service) {}

    public function index(Request $request)
    {
        $search  = $request->query('search');
        $perPage = (int) $request->query('per_page', 15);

        $users = $this->service->listActive($search, $perPage);

        return UserResource::collection($users);
    }
}
