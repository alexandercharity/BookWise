@extends('layouts.app')
@section('title', 'Daftar Buku')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-indigo-700">
        {{ $query ? "Hasil pencarian: \"{$query}\"" : 'Semua Buku' }}
    </h1>
    <p class="text-gray-500 text-sm mt-1">{{ $books->total() }} buku ditemukan</p>
</div>

{{-- Mobile search --}}
<form action="{{ route('books.index') }}" method="GET" class="flex gap-2 mb-6 md:hidden">
    <input name="q" value="{{ $query }}" placeholder="Cari buku..."
           class="flex-1 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
    <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Cari</button>
</form>

<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
    @forelse($books as $book)
        <a href="{{ route('books.show', $book) }}"
           class="bg-white rounded-xl shadow hover:shadow-md transition overflow-hidden group">
            <img src="{{ $book->image_url ?: 'https://via.placeholder.com/128x192?text=No+Cover' }}"
                 alt="{{ $book->title }}"
                 class="w-full h-44 object-cover group-hover:opacity-90 transition">
            <div class="p-3">
                <p class="text-sm font-semibold line-clamp-2 leading-tight">{{ $book->title }}</p>
                <p class="text-xs text-gray-500 mt-1 truncate">{{ $book->author }}</p>
                <p class="text-xs text-indigo-600 mt-1">⭐ {{ $book->avgRating() }}/10</p>
            </div>
        </a>
    @empty
        <div class="col-span-full text-center text-gray-400 py-16">
            Tidak ada buku ditemukan.
        </div>
    @endforelse
</div>

<div class="mt-8">
    {{ $books->links() }}
</div>
@endsection
