@extends('layouts.app')
@section('title', 'Daftar Buku')

@section('content')

{{-- Header --}}
<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div>
        @if($query)
            <p class="text-gray-400 text-sm mb-1">Hasil pencarian untuk</p>
            <h1 class="text-2xl font-bold text-white">"{{ $query }}"</h1>
            <p class="text-gray-500 text-sm mt-1">{{ number_format($books->total()) }} buku ditemukan</p>
        @elseif($genre)
            <p class="text-gray-400 text-sm mb-1">Genre</p>
            <h1 class="text-2xl font-bold text-white capitalize">{{ $genre }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ number_format($books->total()) }} buku ditemukan</p>
        @else
            <h1 class="text-2xl font-bold text-white mb-1">Semua Buku</h1>
            <p class="text-gray-500 text-sm">{{ number_format($books->total()) }} buku tersedia</p>
        @endif
    </div>
    @if($query || $genre)
        <a href="{{ route('books.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300 transition">← Semua Buku</a>
    @endif
</div>

{{-- Genre Filter Chips --}}
@php
$genres = [
    '😱 Horror'     => 'horror',
    '💕 Romance'    => 'romance',
    '🔮 Fantasy'    => 'fantasy',
    '🚀 Sci-Fi'     => 'science',
    '🔍 Mystery'    => 'mystery',
    '🕵️ Thriller'   => 'thriller',
    '😂 Comedy'     => 'comedy',
    '🌍 Adventure'  => 'adventure',
    '📖 Historical' => 'historical',
    '🧠 Non-Fiction'=> 'biography',
    '💡 Philosophy' => 'philosophy',
    '💼 Classic'    => 'classic',
];
@endphp
<div class="flex flex-wrap gap-2 mb-6">
    @foreach($genres as $label => $value)
        <a href="{{ route('books.index', ['genre' => $genre === $value ? null : $value]) }}"
           class="px-3 py-1.5 rounded-full text-sm font-medium transition border
                  {{ $genre === $value
                      ? 'bg-indigo-600 border-indigo-500 text-white'
                      : 'bg-slate-800 border-slate-700 text-gray-300 hover:border-indigo-500 hover:text-white' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Mobile search --}}
<form action="{{ route('books.index') }}" method="GET" class="flex gap-2 mb-6 md:hidden">
    <input type="hidden" name="genre" value="{{ $genre }}">
    <div class="relative flex-1">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
        <input name="q" value="{{ $query }}" placeholder="Cari buku..."
               class="w-full bg-slate-800 border border-slate-700 rounded-xl pl-9 pr-4 py-2.5 text-sm text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
    <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-sm transition">Cari</button>
</form>

{{-- Grid --}}
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
    @forelse($books as $book)
        <a href="{{ route('books.show', $book) }}"
           class="book-card group bg-slate-900 rounded-2xl overflow-hidden border border-slate-800 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-indigo-900/30 hover:-translate-y-1.5">
            <div class="relative overflow-hidden aspect-[2/3]">
                <img src="{{ $book->image_url ?: 'https://placehold.co/128x192/1e293b/6366f1?text=📚' }}"
                     alt="{{ $book->title }}"
                     class="w-full h-full object-cover"
                     loading="lazy">
                {{-- Overlay on hover --}}
                <div class="book-overlay absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent opacity-0 transition-opacity duration-300 flex flex-col justify-end p-3">
                    <span class="text-xs text-white font-medium bg-indigo-600 px-2 py-1 rounded-lg w-fit">Lihat Detail →</span>
                </div>
                {{-- Rating badge --}}
                @if($book->avgRating() > 0)
                <div class="absolute top-2 right-2 bg-black/70 backdrop-blur-sm text-yellow-400 text-xs px-2 py-0.5 rounded-full flex items-center gap-0.5">
                    ★ {{ $book->avgRating() }}
                </div>
                @endif
            </div>
            <div class="p-3">
                <p class="text-sm font-semibold line-clamp-2 leading-tight text-gray-100 group-hover:text-indigo-300 transition-colors">{{ $book->title }}</p>
                <p class="text-xs text-gray-500 mt-1 truncate">{{ $book->author }}</p>
            </div>
        </a>
    @empty
        <div class="col-span-full text-center py-24">
            <p class="text-6xl mb-4">📭</p>
            <p class="text-gray-300 font-medium">Tidak ada buku ditemukan</p>
            <p class="text-gray-600 text-sm mt-1">Coba kata kunci yang berbeda</p>
            <a href="{{ route('books.index') }}" class="inline-block mt-4 bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-xl transition">
                Lihat Semua Buku
            </a>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
<div class="mt-10 flex justify-center">
    {{ $books->links() }}
</div>

@endsection
