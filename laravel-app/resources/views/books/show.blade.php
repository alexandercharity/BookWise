@extends('layouts.app')
@section('title', $book->title)

@section('content')

{{-- Back --}}
<a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white text-sm mb-6 transition">
    ← Kembali
</a>

<div class="grid md:grid-cols-3 gap-10">

    {{-- Cover --}}
    <div class="md:col-span-1">
        <div class="sticky top-24">
            <img src="{{ $book->image_url ?: 'https://placehold.co/200x300/1e293b/6366f1?text=📚' }}"
                 alt="{{ $book->title }}"
                 class="w-full max-w-xs mx-auto rounded-2xl shadow-2xl shadow-black/50 border border-slate-800">

            {{-- Rating Form --}}
            @auth
                <div class="mt-6 bg-slate-900 border border-slate-800 rounded-2xl p-5">
                    <p class="font-semibold text-sm text-gray-200 mb-3">Beri Rating</p>
                    <form action="{{ route('ratings.store', $book) }}" method="POST">
                        @csrf
                        <div class="flex gap-2 items-center">
                            <input type="number" name="rating" min="1" max="10"
                                   value="{{ $userRating }}"
                                   placeholder="1–10"
                                   class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 w-20 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <button class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                                {{ $userRating ? '✏️ Update' : '⭐ Simpan' }}
                            </button>
                        </div>
                    </form>
                    @if($userRating)
                        <form action="{{ route('ratings.destroy', $book) }}" method="POST" class="mt-2">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-300 text-xs transition">Hapus rating</button>
                        </form>
                    @endif
                </div>
            @else
                <div class="mt-6 bg-slate-900 border border-slate-800 rounded-2xl p-5 text-center">
                    <p class="text-sm text-gray-400 mb-3">Login untuk memberi rating</p>
                    <a href="{{ route('login') }}" class="inline-block bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-sm font-medium transition">Login</a>
                </div>
            @endauth
        </div>
    </div>

    {{-- Info --}}
    <div class="md:col-span-2 space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-white leading-tight">{{ $book->title }}</h1>
            <p class="text-indigo-400 mt-2 text-lg">{{ $book->author }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            @if($book->year_of_publication)
                <span class="bg-slate-800 border border-slate-700 text-gray-300 text-sm px-3 py-1.5 rounded-xl">
                    📅 {{ $book->year_of_publication }}
                </span>
            @endif
            @if($book->publisher)
                <span class="bg-slate-800 border border-slate-700 text-gray-300 text-sm px-3 py-1.5 rounded-xl">
                    🏢 {{ $book->publisher }}
                </span>
            @endif
            <span class="bg-yellow-900/40 border border-yellow-700/50 text-yellow-300 text-sm px-3 py-1.5 rounded-xl">
                ⭐ {{ $book->avgRating() }}/10
            </span>
            <span class="bg-slate-800 border border-slate-700 text-gray-300 text-sm px-3 py-1.5 rounded-xl">
                💬 {{ $book->ratings()->count() }} rating
            </span>
        </div>

        {{-- Rating bar --}}
        @php $avg = $book->avgRating(); @endphp
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <p class="text-sm text-gray-400 mb-3">Rating rata-rata</p>
            <div class="flex items-center gap-4">
                <span class="text-4xl font-bold text-white">{{ $avg }}</span>
                <div class="flex-1">
                    <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all"
                             style="width: {{ ($avg / 10) * 100 }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">dari skala 10</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Similar Books --}}
@if($similar->count())
    <div class="mt-14">
        <div class="flex items-center gap-3 mb-6">
            <div class="h-6 w-1 bg-indigo-500 rounded-full"></div>
            <h2 class="text-xl font-bold text-white">Buku Serupa</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($similar as $rec)
                @php $recBook = \App\Models\Book::where('isbn', $rec['isbn'])->first(); @endphp
                @if($recBook)
                    <a href="{{ route('books.show', $recBook) }}"
                       class="book-card group bg-slate-900 rounded-2xl overflow-hidden border border-slate-800 hover:border-indigo-500/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-900/20">
                        <div class="relative overflow-hidden aspect-[2/3]">
                            <img src="{{ $recBook->image_url ?: 'https://placehold.co/128x192/1e293b/6366f1?text=📚' }}"
                                 class="w-full h-full object-cover">
                            <div class="book-overlay absolute inset-0 bg-black/40 opacity-0 transition-opacity duration-300"></div>
                        </div>
                        <div class="p-2.5">
                            <p class="text-xs font-semibold line-clamp-2 text-gray-200 group-hover:text-indigo-300 transition">{{ $recBook->title }}</p>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@endif

@endsection
