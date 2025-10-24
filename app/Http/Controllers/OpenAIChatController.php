<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\District;
use GuzzleHttp\Client;

class OpenAIChatController extends Controller
{
    /**
     * Proxy chat requests to OpenAI using server-side API key.
     *
     * POST /district/{slug}/chat
     */
    public function chat(Request $request, string $slug)
    {
        $request->validate(["question" => "required|string"]);

        $district = District::with(['items'])->where('slug', $slug)->first();
        if (! $district) {
            return response()->json(['error' => 'District not found'], 404);
        }

        $openaiKey = env('OPENAI_API_KEY');
        if (! $openaiKey) {
            return response()->json(['error' => 'OpenAI API key not configured on server'], 500);
        }

        // Build a compact context for the model
        $contextParts = [];
        $contextParts[] = "District: {$district->name}.";
        if ($district->intro_html) {
            // strip tags for safety
            $contextParts[] = 'Intro: ' . strip_tags($district->intro_html);
        }
        $titles = $district->items->pluck('title')->filter()->take(12)->toArray();
        if (! empty($titles)) {
            $contextParts[] = 'Notable items: ' . implode(', ', $titles);
        }

        $system = "You are a helpful assistant that answers questions about a district. Use only the context provided. If the answer is not in the context, say you don't know and offer suggestions to the user.";
        $userPrompt = "Context:\n" . implode("\n", $contextParts) . "\n\nQuestion: " . $request->input('question');

        try {
            $client = new Client(['timeout' => 30]);
            $res = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $openaiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'max_tokens' => 512,
                ],
            ]);

            $body = json_decode((string) $res->getBody(), true);
            $text = $body['choices'][0]['message']['content'] ?? null;
            return response()->json(['reply' => $text, 'raw' => $body]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'OpenAI request failed', 'message' => $e->getMessage()], 500);
        }
    }
}
