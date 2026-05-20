<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        private ChatbotService $chatbot
    ) {}

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $result = $this->chatbot->reply(
            trim($validated['message']),
            $request->user(),
            $request->session()->getId()
        );

        return response()->json($result);
    }

    public function history(Request $request): JsonResponse
    {
        return response()->json(
            $this->chatbot->history($request->user(), $request->session()->getId())
        );
    }
}
