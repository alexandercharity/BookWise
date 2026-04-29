<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — BookWise</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-md">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <a href="{{ route('books.index') }}" class="inline-flex items-center gap-2">
            <span class="text-4xl">📚</span>
            <span class="text-3xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">BookWise</span>
        </a>
        <p class="text-gray-500 text-sm mt-2">Sistem Rekomendasi Buku</p>
    </div>

    {{-- Card --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl shadow-black/50">
        <div class="text-center mb-6">
            <span class="text-4xl">🔑</span>
            <h1 class="text-xl font-bold text-white mt-3">Lupa Password?</h1>
            <p class="text-gray-500 text-sm mt-2">Masukkan email kamu dan kami akan kirimkan link untuk reset password.</p>
        </div>

        @if (session('status'))
            <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-300 px-4 py-3 rounded-xl text-sm mb-5">
                ✅ {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm text-gray-400 mb-1.5">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       required autofocus
                       class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-gray-100 text-sm placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                       placeholder="email@contoh.com">
                @error('email')
                    <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-xl transition text-sm">
                Kirim Link Reset Password
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Ingat password?
            <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 transition font-medium">Kembali ke Login</a>
        </p>
    </div>
</div>

</body>
</html>
