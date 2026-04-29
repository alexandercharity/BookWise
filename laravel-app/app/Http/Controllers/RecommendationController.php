<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __construct(private RecommendationService $ml) {}

    public function index()
    {
        $userId = (string) auth()->id();

        // ── Collaborative Filtering ──────────────────────────────────────
        $cfData  = $this->ml->collaborative($userId, 5);
        $cfRecs  = collect($cfData['recommendations'] ?? []);

        if ($cfRecs->isNotEmpty()) {
            $cfIsbns       = $cfRecs->pluck('isbn');
            $cfBooks       = Book::whereIn('isbn', $cfIsbns)->get()->keyBy('isbn');
            $collaborative = $cfIsbns->map(fn($isbn) => $cfBooks->get($isbn))->filter();
        } else {
            // Fallback: filter berdasarkan genre favorit user, atau top rated
            $genres = auth()->user()->favorite_genres ?? [];
            $query  = Book::withCount('ratings')->orderByDesc('ratings_count');

            if (!empty($genres)) {
                $query->where(function($q) use ($genres) {
                    foreach ($genres as $genre) {
                        $q->orWhere('title', 'like', "%{$genre}%")
                          ->orWhere('author', 'like', "%{$genre}%");
                    }
                });
            }

            $collaborative = $query->limit(5)->get();

            // Kalau hasil filter kosong, fallback ke top rated
            if ($collaborative->isEmpty()) {
                $collaborative = Book::withCount('ratings')
                    ->orderByDesc('ratings_count')
                    ->limit(5)
                    ->get();
            }
        }

        // ── Content-Based Filtering ──────────────────────────────────────
        $lastBook     = auth()->user()->histories()->with('book')->latest()->first()?->book;
        $contentBased = collect();

        if ($lastBook) {
            // Coba pakai isbn dulu, fallback ke book_id
            $cbData = $this->ml->contentBased($lastBook->isbn, 6);

            // Kalau isbn tidak ketemu di model, coba book_id
            if (empty($cbData['recommendations'])) {
                $cbData = $this->ml->contentBasedById((int) $lastBook->id, 6);
            }

            $cbIsbns      = collect($cbData['recommendations'] ?? [])->pluck('isbn');
            $cbBooks      = Book::whereIn('isbn', $cbIsbns)->get()->keyBy('isbn');
            $contentBased = $cbIsbns->map(fn($isbn) => $cbBooks->get($isbn))->filter();

            // Fallback: cari buku serupa berdasarkan author yang sama
            if ($contentBased->isEmpty()) {
                $contentBased = Book::where('author', $lastBook->author)
                    ->where('id', '!=', $lastBook->id)
                    ->limit(6)
                    ->get();
            }
        }

        return view('recommendations.index', compact('collaborative', 'contentBased', 'lastBook'));
    }
}
