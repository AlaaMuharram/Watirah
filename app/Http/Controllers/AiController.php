<?php

namespace App\Http\Controllers;

use App\Services\AiAssistantService;
use App\Services\AiOrderExtractorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(
        protected AiOrderExtractorService $extractor,
        protected AiAssistantService $assistant
    ) {}

    public function extract(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|min:5|max:5000',
        ]);

        $result = $this->extractor->extract($validated['text']);

        return response()->json($result);
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|string|min:3|max:1000',
        ]);

        $result = $this->assistant->ask($validated['question']);

        return response()->json($result);
    }
}
