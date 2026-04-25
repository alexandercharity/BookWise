<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookWise — @yield('title', 'Sistem Rekomendasi Buku')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .book-card:hover .book-overlay { opacity: 1; }
        .book-card:hover img { transform: scale(1.05); }
        img { transition: transform 0.3s ease; }
        .gradient-hero { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.15); }
        .star-rating { color: #fbbf24; }
    </style>
</head>
<body class="bg-slate-950 text-gray-100 min-h-screen flex flex-col">

{{-- Navbar --}}
<nav class="sticky top-0 z-50 bg-slate-900/95 backdrop-blur border-b border-slate-800 shadow-xl">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">

        <a href="{{ route('books.index') }}" class="flex items-center gap-2 text-xl font-bold text-white shrink-0">
            <span class="text-2xl">📚</span>
            <span class="bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">BookWise</span>
        </a>

        <form action="{{ route('books.index') }}" method="GET" class="hidden md:flex flex-1 max-w-md gap-2">
            <div class="relative flex-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
                <input name="q" value="{{ request('q') }}" placeholder="Cari judul atau penulis..."
                       class="w-full bg-slate-800 border border-slate-700 rounded-xl pl-9 pr-4 py-2 text-sm text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-sm font-medium transition">Cari</button>
        </form>

        <div class="flex items-center gap-2 text-sm shrink-0">
            <a href="{{ route('books.index') }}" class="px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-800 transition">Buku</a>
            @auth
                <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-800 transition">Rekomendasi</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="px-3 py-2 rounded-lg bg-red-600/20 text-red-400 hover:bg-red-600/30 hover:text-red-300 transition text-sm">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-slate-800 transition">Login</a>
                <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium transition">Daftar</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Flash messages --}}
@if(session('success'))
    <div class="max-w-7xl mx-auto mt-4 px-4 w-full">
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-300 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            ✅ {{ session('success') }}
        </div>
    </div>
@endif

<main class="flex-1 max-w-7xl mx-auto w-full px-4 py-8">
    @yield('content')
</main>

<footer class="border-t border-slate-800 text-center text-xs text-gray-500 py-6 mt-8">
    BookWise &copy; {{ date('Y') }} — Sistem Rekomendasi Buku berbasis Machine Learning
</footer>

</body>
</html>
