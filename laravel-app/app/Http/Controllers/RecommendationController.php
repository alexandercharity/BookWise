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

        // Collaborative: berdasarkan histori rating user
        $cfData  = $this->ml->collaborative($userId, 10);
        $cfIsbns = collect($cfData['recommendations'] ?? [])->pluck('isbn');

        $cfBooks       = Book::whereIn('isbn', $cfIsbns)->get()->keyBy('isbn');
        $collaborative = $cfIsbns->map(fn($isbn) => $cfBooks->get($isbn))->filter();

        // Content-based: dari buku terakhir yang dilihat user
        $lastBook     = auth()->user()->histories()->with('book')->latest()->first()?->book;
        $contentBased = collect();
        if ($lastBook) {
            $cbData   = $this->ml->contentBased($lastBook->isbn, 6);
            $cbIsbns  = collect($cbData['recommendations'] ?? [])->pluck('isbn');
            $cbBooks  = Book::whereIn('isbn', $cbIsbns)->get()->keyBy('isbn');
            $contentBased = $cbIsbns->map(fn($isbn) => $cbBooks->get($isbn))->filter();
        }

        return view('recommendations.index', compact('collaborative', 'contentBased', 'lastBook'));
    }
}
