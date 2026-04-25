@extends('layouts.app')
@section('title', $book->title)

@section('content')
<div class="grid md:grid-cols-3 gap-8">

    {{-- Cover & Info --}}
    <div class="md:col-span-1">
        <img src="{{ $book->image_url ?: 'https://via.placeholder.com/200x300?text=No+Cover' }}"
             alt="{{ $book->title }}"
             class="w-full max-w-xs mx-auto rounded-xl shadow-lg">
    </div>

    <div class="md:col-span-2 space-y-4">
        <h1 class="text-2xl font-bold text-indigo-700">{{ $book->title }}</h1>
        <p class="text-gray-600">oleh <span class="font-medium">{{ $book->author }}</span></p>
        <div class="flex gap-4 text-sm text-gray-500">
            <span>📅 {{ $book->year_of_publication }}</span>
            <span>🏢 {{ $book->publisher }}</span>
            <span>⭐ {{ $book->avgRating() }}/10 ({{ $book->ratings()->count() }} rating)</span>
        </div>

        {{-- Rating Form --}}
        @auth
            <div class="bg-indigo-50 rounded-xl p-4">
                <p class="font-semibold text-sm mb-2">Beri Rating (1–10):</p>
                <form action="{{ route('ratings.store', $book) }}" method="POST" class="flex gap-2 items-center">
                    @csrf
                    <input type="number" name="rating" min="1" max="10"
                           value="{{ $userRating }}"
                           class="border rounded-lg px-3 py-1.5 w-20 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <button class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-indigo-700">
                        {{ $userRating ? 'Update' : 'Simpan' }}
                    </button>
                    @if($userRating)
                        <form action="{{ route('ratings.destroy', $book) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-red-500 text-sm hover:underline">Hapus</button>
                        </form>
                    @endif
                </form>
            </div>
        @else
            <p class="text-sm text-gray-500">
                <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Login</a> untuk memberi rating.
            </p>
        @endauth
    </div>
</div>

{{-- Similar Books --}}
@if($similar->count())
    <div class="mt-10">
        <h2 class="text-xl font-bold text-indigo-700 mb-4">Buku Serupa</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
            @foreach($similar as $rec)
                @php $recBook = \App\Models\Book::where('isbn', $rec['isbn'])->first(); @endphp
                @if($recBook)
                    <a href="{{ route('books.show', $recBook) }}"
                       class="bg-white rounded-xl shadow hover:shadow-md transition overflow-hidden">
                        <img src="{{ $recBook->image_url ?: 'https://via.placeholder.com/128x192?text=No+Cover' }}"
                             class="w-full h-36 object-cover">
                        <div class="p-2">
                            <p class="text-xs font-semibold line-clamp-2">{{ $recBook->title }}</p>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@endif
@endsection
