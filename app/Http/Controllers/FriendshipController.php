<?php

namespace App\Http\Controllers;

use App\Http\Requests\Friends\AcceptFriendRequest;
use App\Http\Requests\Friends\SendFriendRequest;
use App\Services\FriendshipService;
use Illuminate\Http\Request;
use App\Http\Resources\FriendResource;

class FriendshipController extends Controller
{
    public function __construct(private FriendshipService $service) {}

    public function send(SendFriendRequest $request)
    {
        $auth = $request->user();
        abort_unless($auth->hasVerifiedEmail(), 403);

        $friendship = $this->service->send($auth, (int) $request->integer('user_id'));

        return (new FriendResource($friendship))
            ->response($request)
            ->setStatusCode(201);
    }

    public function accept(AcceptFriendRequest $request)
    {
        $auth = $request->user();
        abort_unless($auth->hasVerifiedEmail(), 403);
        return new FriendResource($this->service->accept($auth, (int) $request->integer('user_id')));
    }

    public function list(Request $request)
    {
        return FriendResource::collection($this->service->listFriends($request->user()));
    }
}
