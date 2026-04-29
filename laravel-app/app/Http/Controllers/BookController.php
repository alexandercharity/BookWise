<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\UserBookHistory;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(private RecommendationService $ml) {}

    public function index(Request $request)
    {
        $query = $request->get('q');
        $genre = $request->get('genre');

        $books = Book::query()
            ->when($query, fn($q) => $q->where('title', 'like', "%{$query}%")
                                       ->orWhere('author', 'like', "%{$query}%"))
            ->when($genre, fn($q) => $q->where('title', 'like', "%{$genre}%")
                                       ->orWhere('author', 'like', "%{$genre}%"))
            ->withCount('ratings')
            ->orderByDesc('ratings_count')
            ->paginate(20)
            ->withQueryString();

        return view('books.index', compact('books', 'query', 'genre'));
    }

    public function show(Book $book)
    {
        // Catat history view
        if (auth()->check()) {
            UserBookHistory::firstOrCreate([
                'user_id' => auth()->id(),
                'book_id' => $book->id,
                'action'  => 'viewed',
            ]);
        }

        $userRating    = auth()->check()
            ? $book->ratings()->where('user_id', auth()->id())->value('rating')
            : null;

        // Rekomendasi content-based berdasarkan buku ini
        $similar = collect($this->ml->contentBased($book->isbn, 6)['recommendations'] ?? []);

        return view('books.show', compact('book', 'userRating', 'similar'));
    }
}
