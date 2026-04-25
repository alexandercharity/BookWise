<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookWise — @yield('title', 'Sistem Rekomendasi Buku')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

{{-- Navbar --}}
<nav class="bg-indigo-700 text-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="{{ route('books.index') }}" class="text-xl font-bold tracking-tight">📚 BookWise</a>

        <form action="{{ route('books.index') }}" method="GET" class="hidden md:flex gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari judul atau penulis..."
                   class="rounded-lg px-3 py-1.5 text-gray-800 text-sm w-64 focus:outline-none">
            <button class="bg-white text-indigo-700 px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-indigo-50">Cari</button>
        </form>

        <div class="flex items-center gap-4 text-sm">
            <a href="{{ route('books.index') }}" class="hover:underline">Buku</a>
            @auth
                <a href="{{ route('dashboard') }}" class="hover:underline">Rekomendasi</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-white text-indigo-700 px-3 py-1.5 rounded-lg font-medium hover:bg-indigo-50">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hover:underline">Login</a>
                <a href="{{ route('register') }}" class="bg-white text-indigo-700 px-3 py-1.5 rounded-lg font-medium hover:bg-indigo-50">Daftar</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Flash messages --}}
@if(session('success'))
    <div class="max-w-7xl mx-auto mt-4 px-4">
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    </div>
@endif

<main class="flex-1 max-w-7xl mx-auto w-full px-4 py-6">
    @yield('content')
</main>

<footer class="bg-indigo-700 text-white text-center text-xs py-3 mt-8">
    BookWise &copy; {{ date('Y') }} — Sistem Rekomendasi Buku
</footer>

</body>
</html>
