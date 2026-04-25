@extends('layouts.app')
@section('title', 'Rekomendasi untuk Kamu')

@section('content')
<h1 class="text-2xl font-bold text-indigo-700 mb-2">Rekomendasi untuk Kamu</h1>
<p class="text-gray-500 text-sm mb-8">Berdasarkan histori dan preferensi kamu</p>

{{-- Collaborative Filtering --}}
<section class="mb-10">
    <h2 class="text-lg font-semibold mb-1">🤝 Collaborative Filtering</h2>
    <p class="text-xs text-gray-400 mb-4">Berdasarkan pengguna lain dengan selera serupa</p>

    @if($collaborative->isEmpty())
        <p class="text-gray-400 text-sm">Belum ada rekomendasi. Coba beri rating beberapa buku dulu.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            @foreach($collaborative as $book)
                <a href="{{ route('books.show', $book) }}"
                   class="bg-white rounded-xl shadow hover:shadow-md transition overflow-hidden">
                    <img src="{{ $book->image_url ?: 'https://via.placeholder.com/128x192?text=No+Cover' }}"
                         class="w-full h-40 object-cover">
                    <div class="p-3">
                        <p class="text-sm font-semibold line-clamp-2">{{ $book->title }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $book->author }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>

{{-- Content-Based Filtering --}}
<section>
    <h2 class="text-lg font-semibold mb-1">📖 Content-Based Filtering</h2>
    <p class="text-xs text-gray-400 mb-4">
        Berdasarkan buku terakhir yang kamu lihat:
        @if($lastBook)
            <span class="text-indigo-600 font-medium">{{ $lastBook->title }}</span>
        @else
            —
        @endif
    </p>

    @if($contentBased->isEmpty())
        <p class="text-gray-400 text-sm">Belum ada rekomendasi. Coba lihat beberapa buku dulu.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
            @foreach($contentBased as $book)
                <a href="{{ route('books.show', $book) }}"
                   class="bg-white rounded-xl shadow hover:shadow-md transition overflow-hidden">
                    <img src="{{ $book->image_url ?: 'https://via.placeholder.com/128x192?text=No+Cover' }}"
                         class="w-full h-36 object-cover">
                    <div class="p-2">
                        <p class="text-xs font-semibold line-clamp-2">{{ $book->title }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>
@endsection
