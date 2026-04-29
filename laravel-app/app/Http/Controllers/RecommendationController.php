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
        $cfData  = $this->ml->collaborative($userId, 10);
        $cfRecs  = collect($cfData['recommendations'] ?? []);

        if ($cfRecs->isNotEmpty()) {
            $cfIsbns       = $cfRecs->pluck('isbn');
            $cfBooks       = Book::whereIn('isbn', $cfIsbns)->get()->keyBy('isbn');
            $collaborative = $cfIsbns->map(fn($isbn) => $cfBooks->get($isbn))->filter();
        } else {
            // Fallback: tampilkan buku dengan rating terbanyak
            $collaborative = Book::withCount('ratings')
                ->orderByDesc('ratings_count')
                ->limit(10)
                ->get();
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
