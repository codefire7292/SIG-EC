<?php

namespace App\Http\Controllers\Scolarite;

use App\Http\Controllers\Controller;
use App\Services\Scolarite\GeminiService;
use Illuminate\Http\Request;

class AssaneChatController extends Controller
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
        ]);

        $message = $request->input('message');
        $history = $request->input('history', []);

        $response = $this->gemini->chatWithAssane($message, $history);

        return response()->json([
            'response' => $response,
            'status' => 'success'
        ]);
    }
}
