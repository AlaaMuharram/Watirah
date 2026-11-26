<?php

namespace App\Http\Controllers;

use App\Services\WatiraDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class WatiraApiController extends Controller
{
    public function __construct(private WatiraDatabase $db) {}

    public function all(): JsonResponse
    {
        try {
            return $this->json($this->db->all());
        } catch (Throwable $e) {
            report($e);

            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function save(Request $request, string $name): JsonResponse
    {
        try {
            $body = $request->json()->all();

            if ($name === 'settings') {
                $this->db->saveSettings($body);
            } elseif ($this->db->known($name) && array_is_list($body)) {
                $this->db->saveTable($name, $body);
            } else {
                return $this->json(['error' => 'جدول غير معروف أو بيانات غير صالحة'], 400);
            }

            return $this->json(['ok' => true]);
        } catch (Throwable $e) {
            report($e);

            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function clear(): JsonResponse
    {
        try {
            $this->db->clear();

            return $this->json(['ok' => true]);
        } catch (Throwable $e) {
            report($e);

            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /** @param array<string, mixed> $payload */
    private function json(array $payload, int $status = 200): JsonResponse
    {
        return response()->json($payload, $status, [], JSON_UNESCAPED_UNICODE);
    }
}
