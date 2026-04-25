<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Book $book)
    {
        $request->validate(['rating' => 'required|integer|min:1|max:10']);

        Rating::updateOrCreate(
            ['user_id' => auth()->id(), 'book_id' => $book->id],
            ['rating'  => $request->rating]
        );

        return back()->with('success', 'Rating berhasil disimpan!');
    }

    public function destroy(Book $book)
    {
        Rating::where('user_id', auth()->id())
              ->where('book_id', $book->id)
              ->delete();

        return back()->with('success', 'Rating dihapus.');
    }
}
