<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    public static function askGemini($message)
    {
        $apiKey = env('GEMINI_API_KEY');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.gemini.com/v1/query', [
            'prompt' => $message,
        ]);

        return $response->json()['output'] ?? null;
    }
}
