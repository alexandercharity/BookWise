@extends('layouts.app')
@section('title', 'Rekomendasi untuk Kamu')

@section('content')

{{-- Hero --}}
<div class="gradient-hero rounded-3xl p-8 mb-10 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,<svg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"><g fill=\"none\" fill-rule=\"evenodd\"><g fill=\"%23ffffff\" fill-opacity=\"0.4\"><path d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/></g></g></svg>');"></div>
    <div class="relative">
        <p class="text-indigo-300 text-sm font-medium mb-2">Halo, {{ auth()->user()->name }} 👋</p>
        <h1 class="text-3xl font-bold text-white mb-2">Rekomendasi untuk Kamu</h1>
        <p class="text-indigo-200/70 text-sm">Dipersonalisasi berdasarkan histori dan preferensi kamu</p>
    </div>
</div>

{{-- Collaborative Filtering --}}
<section class="mb-12">
    <div class="flex items-center gap-3 mb-2">
        <div class="h-6 w-1 bg-purple-500 rounded-full"></div>
        <h2 class="text-xl font-bold text-white">Collaborative Filtering</h2>
        <span class="bg-purple-900/50 border border-purple-700/50 text-purple-300 text-xs px-2 py-0.5 rounded-full">🤝 User-based</span>
    </div>
    <p class="text-gray-500 text-sm mb-6 ml-4">Berdasarkan pengguna lain dengan selera serupa</p>

    @if($collaborative->isEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center">
            <p class="text-4xl mb-3">🤝</p>
            <p class="text-gray-400 text-sm">Belum ada rekomendasi.</p>
            <p class="text-gray-600 text-xs mt-1">Coba beri rating beberapa buku dulu.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($collaborative as $book)
                <a href="{{ route('books.show', $book) }}"
                   class="book-card group bg-slate-900 rounded-2xl overflow-hidden border border-slate-800 hover:border-purple-500/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-purple-900/20">
                    <div class="relative overflow-hidden aspect-[2/3]">
                        <img src="{{ $book->image_url ?: 'https://placehold.co/128x192/1e293b/a855f7?text=📚' }}"
                             alt="{{ $book->title }}"
                             class="w-full h-full object-cover">
                        <div class="book-overlay absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 transition-opacity duration-300"></div>
                    </div>
                    <div class="p-3">
                        <p class="text-sm font-semibold line-clamp-2 text-gray-100 group-hover:text-purple-300 transition">{{ $book->title }}</p>
                        <p class="text-xs text-gray-500 mt-1 truncate">{{ $book->author }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>

{{-- Content-Based Filtering --}}
<section>
    <div class="flex items-center gap-3 mb-2">
        <div class="h-6 w-1 bg-indigo-500 rounded-full"></div>
        <h2 class="text-xl font-bold text-white">Content-Based Filtering</h2>
        <span class="bg-indigo-900/50 border border-indigo-700/50 text-indigo-300 text-xs px-2 py-0.5 rounded-full">📖 Item-based</span>
    </div>
    <p class="text-gray-500 text-sm mb-6 ml-4">
        Berdasarkan buku terakhir yang kamu lihat:
        @if($lastBook)
            <a href="{{ route('books.show', $lastBook) }}" class="text-indigo-400 hover:text-indigo-300 font-medium transition">{{ $lastBook->title }}</a>
        @else
            <span class="text-gray-600">—</span>
        @endif
    </p>

    @if($contentBased->isEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center">
            <p class="text-4xl mb-3">📖</p>
            <p class="text-gray-400 text-sm">Belum ada rekomendasi.</p>
            <p class="text-gray-600 text-xs mt-1">Coba lihat beberapa buku dulu.</p>
            <a href="{{ route('books.index') }}" class="inline-block mt-4 bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-xl transition">Jelajahi Buku</a>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($contentBased as $book)
                <a href="{{ route('books.show', $book) }}"
                   class="book-card group bg-slate-900 rounded-2xl overflow-hidden border border-slate-800 hover:border-indigo-500/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-900/20">
                    <div class="relative overflow-hidden aspect-[2/3]">
                        <img src="{{ $book->image_url ?: 'https://placehold.co/128x192/1e293b/6366f1?text=📚' }}"
                             alt="{{ $book->title }}"
                             class="w-full h-full object-cover">
                        <div class="book-overlay absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 transition-opacity duration-300"></div>
                    </div>
                    <div class="p-3">
                        <p class="text-sm font-semibold line-clamp-2 text-gray-100 group-hover:text-indigo-300 transition">{{ $book->title }}</p>
                        <p class="text-xs text-gray-500 mt-1 truncate">{{ $book->author }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>

@endsection
