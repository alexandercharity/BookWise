<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.ml_api.url', 'http://127.0.0.1:8000');
    }

    public function contentBased(string $isbn, int $topK = 10): array
    {
        return $this->call('/recommend/content', ['isbn' => $isbn, 'top_k' => $topK]);
    }

    public function contentBasedById(int $bookId, int $topK = 10): array
    {
        return $this->call('/recommend/content', ['book_id' => $bookId, 'top_k' => $topK]);
    }

    public function collaborative(string $userId, int $topK = 10): array
    {
        return $this->call('/recommend/collaborative', ['user_id' => $userId, 'top_k' => $topK]);
    }

    public function hybrid(string $userId, string $isbn, int $topK = 10): array
    {
        return $this->call('/recommend/hybrid', ['user_id' => $userId, 'isbn' => $isbn, 'top_k' => $topK]);
    }

    public function search(string $query, int $limit = 20): array
    {
        return $this->call('/books/search', ['q' => $query, 'limit' => $limit]);
    }

    private function call(string $endpoint, array $params): array
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . $endpoint, $params);
            if ($response->successful()) {
                return $response->json();
            }
            Log::warning("ML API error: {$response->status()} on {$endpoint}");
        } catch (\Exception $e) {
            Log::error("ML API unreachable: " . $e->getMessage());
        }
        return ['recommendations' => [], 'results' => []];
    }
}
