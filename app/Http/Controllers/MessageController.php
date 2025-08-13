<?php

namespace App\Http\Controllers;

use App\Http\Requests\Messages\SendMessageRequest;
use App\Services\MessagingService;
use Illuminate\Http\Request;
use App\Http\Resources\MessageResource;

class MessageController extends Controller
{
    public function __construct(private MessagingService $service) {}

    public function send(SendMessageRequest $request)
    {
        $sender = $request->user();
        abort_unless($sender->hasVerifiedEmail(), 403);

        $message = $this->service->send(
            $sender,
            (int) $request->integer('receiver_id'),
            (string) $request->string('body')
        );

        return (new MessageResource($message))
            ->response($request)
            ->setStatusCode(201);
    }

    public function conversation(Request $request, int $userId)
    {
        $perPage = (int) $request->query('per_page', 20);

        return MessageResource::collection($this->service->conversation($request->user(), $userId, $perPage));
    }
}
